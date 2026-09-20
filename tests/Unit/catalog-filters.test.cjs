const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

const source = readFileSync(new URL('../../public/js/catalog-filters.js', `file://${__filename}`), 'utf8');

function setup({ href = 'http://localhost/eshop/katalog', category = '', buttons = [], storage = new Map(), storageBlocked = false, bootstrap, tooltipTargets = [] } = {}) {
    const nodes = new Map();
    const handlers = new Map();
    const pending = [];
    const timers = new Map();
    const timerDelays = new Map();
    const frames = new Map();
    const historyUrls = [];
    const windowEvents = [];
    const categoryButtons = buttons;
    const location = { href };
    let nextTimer = 0;
    const node = name => {
        if (!nodes.has(name)) nodes.set(name, { innerHTML: '', textContent: '', hidden: false, attributes: {}, dataset: {}, style: {}, offsetWidth: 320, scrollLeft: 0, scrollWidth: 320, clientWidth: 320, inputs: [], facets: [], contains() { return false; }, getBoundingClientRect() { return { left: 0, top: 0, width: 1000 }; }, querySelectorAll(selector) { return selector.startsWith('[data-facet]') ? this.facets : this.inputs; }, setAttribute(key, value) { this.attributes[key] = value; }, addEventListener(event, handler) { handlers.set(`${name}:${event}`, handler); } });
        const element = nodes.get(name);
        element.style.setProperty = function (key, value) { this[key] = value; };
        element.removeAttribute = key => { delete element.attributes[key]; };
        return element;
    };
    const form = {
        action: location.href,
        elements: { q: { value: '' }, category: { value: category }, sort: { value: 'recommended' } },
        filterEntries: [],
        addEventListener(name, handler) { handlers.set(`form:${name}`, handler); },
    };
    const catalog = {
        getBoundingClientRect() { return { top: 80 }; },
        style: { setProperty(name, value) { this[name] = value; } },
        querySelector(selector) { return selector === '[data-catalog-form]' ? form : node(selector); },
        querySelectorAll(selector) { return selector === 'a[title], button[title]' ? tooltipTargets : categoryButtons; },
        addEventListener(name, handler) { handlers.set(`catalog:${name}`, handler); },
    };
    const context = vm.createContext({
        document: { querySelector: () => catalog, addEventListener(name, handler) { handlers.set(`document:${name}`, handler); } },
        window: {
            bootstrap,
            requestAnimationFrame(callback) { const id = ++nextTimer; frames.set(id, callback); return id; },
            cancelAnimationFrame(id) { frames.delete(id); },
            innerHeight: 900, scrollY: 0,
            localStorage: {
                getItem(key) { if (storageBlocked) throw new Error('Storage blocked'); return storage.get(key) ?? null; },
                setItem(key, value) { if (storageBlocked) throw new Error('Storage blocked'); storage.set(key, value); },
            },
            addEventListener(name, handler) { handlers.set(`window:${name}`, handler); },
            dispatchEvent(event) { windowEvents.push(event.type); },
        },
        location,
        history: { pushState(state, title, url) { location.href = url.href; historyUrls.push(url.href); } },
        URL,
        Event,
        AbortController,
        FormData: class {
            constructor(input) { this.entries = [...Object.entries(input.elements).map(([key, value]) => [key, value.value]), ...input.filterEntries]; }
            [Symbol.iterator]() { return this.entries[Symbol.iterator](); }
        },
        setTimeout(callback, delay) { const id = ++nextTimer; timers.set(id, callback); timerDelays.set(id, delay); return id; },
        clearTimeout(id) { timers.delete(id); },
        fetch(url, options) { return new Promise(resolve => pending.push({ url, options, resolve })); },
    });
    vm.runInContext(source, context);
    const flush = () => new Promise(resolve => setImmediate(resolve));
    const respond = async (index, data, ok = true) => {
        pending[index].resolve({ ok, json: async () => ({ results: '', filters: '', active: '', total: 0, ...data }) });
        await flush();
    };
    const frame = timestamp => {
        const callbacks = [...frames.values()];
        frames.clear();
        callbacks.forEach(callback => callback(timestamp));
    };
    return { node, handlers, pending, timers, timerDelays, frames, frame, historyUrls, windowEvents, location, form, respond, categoryButtons, catalog, window: context.window, document: context.document };
}

test('category mouse clicks keep their target until an actual drag begins', () => {
    const app = setup();
    const tabs = app.node('.shop-category-tabs');
    let captured = false;
    tabs.setPointerCapture = () => { captured = true; };
    tabs.releasePointerCapture = () => { captured = false; };
    const button = { dataset: { catalogCategory: 'hinges' }, closest() { return this; } };
    app.handlers.get('.shop-category-tabs:pointerdown')({ pointerType: 'mouse', button: 0, pointerId: 1, clientX: 220 });
    assert.equal(captured, false);
    assert.equal(tabs.dataset.categoryDragging, undefined);
    app.handlers.get('.shop-category-tabs:pointermove')({ pointerId: 1, clientX: 218 });
    assert.equal(captured, false);
    app.handlers.get('.shop-category-tabs:pointerup')({ pointerId: 1 });
    app.handlers.get('catalog:click')({ button: 0, target: captured ? tabs : button, preventDefault() {} });
    assert.equal(app.form.elements.category.value, 'hinges');
    assert.equal(app.pending.length, 1);
    app.handlers.get('.shop-category-tabs:pointerdown')({ pointerType: 'mouse', button: 0, pointerId: 2, clientX: 220 });
    app.handlers.get('.shop-category-tabs:pointermove')({ pointerId: 2, clientX: 120, preventDefault() {} });
    assert.equal(captured, true);
    assert.equal(tabs.dataset.categoryDragging, 'true');
    app.handlers.get('.shop-category-tabs:pointerup')({ pointerId: 2 });
    assert.equal(captured, false);
    app.handlers.get('.shop-category-tabs:pointerdown')({ pointerType: 'mouse', button: 0, pointerId: 3, clientX: 220 });
    app.handlers.get('window:pointerup')({ pointerId: 3 });
    const scrollBefore = tabs.scrollLeft;
    app.handlers.get('.shop-category-tabs:pointermove')({ pointerId: 3, clientX: 120, preventDefault() {} });
    assert.equal(captured, false);
    assert.equal(tabs.scrollLeft, scrollBefore);
});

test('catalog tooltips support hover and focus and are disposed before AJAX replacement', async () => {
    const instances = [];
    const bootstrap = { Tooltip: class {
        constructor(element, options) { this.element = element; this.options = options; this.hidden = false; this.disposed = false; instances.push(this); }
        hide() { this.hidden = true; }
        dispose() { this.disposed = true; }
    } };
    const app = setup({ bootstrap, tooltipTargets: [{}] });
    assert.equal(instances.length, 1);
    assert.equal(instances[0].options.trigger, 'hover focus');
    assert.equal(instances[0].options.container, 'body');
    assert.equal(instances[0].options.customClass, 'shop-tooltip');
    assert.equal(instances[0].options.html, false);
    app.handlers.get('catalog:click')({ target: { closest: () => null } });
    assert.equal(instances[0].hidden, true);
    app.handlers.get('form:submit')({ preventDefault() {} });
    assert.equal(instances[0].disposed, true);
    await app.respond(0, { filters: 'NEW FILTERS' });
    assert.equal(instances[0].disposed, true);
    assert.equal(instances.length, 2);
    app.handlers.get('document:keydown')({ key: 'Escape' });
    assert.equal(instances[1].hidden, true);
    instances[1].hidden = false;
    app.handlers.get('catalog:scroll')();
    assert.equal(instances[1].hidden, true);
    app.handlers.get('form:submit')({ preventDefault() {} });
    await app.respond(1, { errors: { q: ['Invalid search'] } }, false);
    assert.equal(instances[1].disposed, true);
    assert.equal(instances.length, 3);
    assert.equal(instances[2].disposed, false);
});

test('filter path stays open while hovered and closes on mouse leave or keyboard focus exit', () => {
    const app = setup();
    const path = app.node('[data-catalog-path]');
    path.open = false;
    app.handlers.get('[data-catalog-path]:pointerenter')({ pointerType: 'touch' });
    assert.equal(path.open, false);
    app.handlers.get('[data-catalog-path]:pointerenter')({ pointerType: 'mouse' });
    assert.equal(path.open, true);
    path.contains = () => true;
    app.handlers.get('[data-catalog-path]:pointerleave')({ pointerType: 'mouse' });
    assert.equal(path.open, false);
    path.contains = () => false;
    app.handlers.get('[data-catalog-path]:pointerleave')({ pointerType: 'mouse' });
    assert.equal(path.open, false);
    app.handlers.get('[data-catalog-path]:pointerenter')({ pointerType: 'mouse' });
    app.handlers.get('[data-catalog-path]:focusout')({ relatedTarget: null });
    assert.equal(path.open, true);
    let prevented = false;
    app.handlers.get('[data-catalog-path]:click')({ target: { closest: () => ({}) }, preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    app.handlers.get('[data-catalog-path]:pointerleave')({ pointerType: 'mouse' });
    path.open = true;
    app.handlers.get('[data-catalog-path]:focusout')({ relatedTarget: null });
    assert.equal(path.open, false);
    let focused = false;
    path.querySelector = () => ({ focus() { focused = true; } });
    path.open = true;
    app.handlers.get('[data-catalog-path]:keydown')({ key: 'Escape' });
    assert.equal(path.open, false);
    assert.equal(focused, true);
    assert.equal(app.pending.length, 0);
});

test('filter path retains its hover area through updates including removal of the last filter', async () => {
    const app = setup({ category: 'hinges' });
    const path = app.node('[data-catalog-path]');
    const active = app.node('[data-catalog-active]');
    active.getBoundingClientRect = () => ({ height: 48 });
    app.handlers.get('[data-catalog-path]:pointerenter')({ pointerType: 'mouse' });
    for (const [index, chips] of ['ONE FILTER', ''].entries()) {
        app.handlers.get('form:submit')({ preventDefault() {} });
        app.handlers.get('[data-catalog-path]:focusout')({ relatedTarget: null });
        await app.respond(index, { active: chips });
        assert.equal(path.open, true);
        assert.equal(path.attributes['data-path-hover'], '');
        assert.equal(active.style.minHeight, '48px');
        assert.equal(active.innerHTML, chips);
    }
    app.handlers.get('[data-catalog-path]:pointerleave')({ pointerType: 'mouse' });
    assert.equal(path.open, false);
    assert.equal(path.attributes['data-path-hover'], undefined);
    assert.equal(active.style.minHeight, '');
});

test('filter path visibility persists without fetching or changing selected filters', async () => {
    const storage = new Map();
    const app = setup({ category: 'hinges', storage });
    const toggle = app.node('[data-catalog-path-toggle]');
    const active = app.node('[data-catalog-active]');
    assert.equal(toggle.checked, true);
    assert.equal(active.hidden, false);
    toggle.checked = false;
    app.handlers.get('form:change')({ target: toggle });
    assert.equal(active.hidden, true);
    assert.equal(app.form.elements.category.value, 'hinges');
    assert.equal(app.pending.length, 0);
    assert.equal(app.historyUrls.length, 0);
    const reloaded = setup({ storage });
    assert.equal(reloaded.node('[data-catalog-path-toggle]').checked, false);
    assert.equal(reloaded.node('[data-catalog-active]').hidden, true);
    app.handlers.get('form:submit')({ preventDefault() {} });
    await app.respond(0, { active: 'SELECTED FILTERS', total: 12 });
    assert.equal(active.hidden, true);
    assert.equal(active.innerHTML, 'SELECTED FILTERS');
    assert.equal(app.node('[data-catalog-total]').textContent, '12 položiek');
    assert.equal(app.node('[data-catalog-status]').textContent, '12 nájdených produktov');
    toggle.checked = true;
    app.handlers.get('form:change')({ target: toggle });
    assert.equal(active.hidden, false);
    assert.equal(setup({ storage }).node('[data-catalog-active]').hidden, false);
});

test('filter path toggle works when browser storage is blocked', () => {
    const app = setup({ storageBlocked: true });
    const toggle = app.node('[data-catalog-path-toggle]');
    assert.equal(toggle.checked, true);
    toggle.checked = false;
    app.handlers.get('form:change')({ target: toggle });
    assert.equal(app.node('[data-catalog-active]').hidden, true);
    assert.equal(app.pending.length, 0);
});

test('connector is centered between button bottom and band bottom as chips change and hides on mobile, clipped tabs and close', async () => {
    const app = setup();
    let desktop = true;
    app.window.matchMedia = () => ({ matches: desktop });
    const connector = app.node('[data-category-connector]');
    connector.style.setProperty = function (name, value) { this[name] = value; };
    app.node('[data-category-bar]').getBoundingClientRect = () => ({ left: 100, top: 200, bottom: 272 });
    let tabsRight = 1100;
    app.node('.shop-category-tabs').getBoundingClientRect = () => ({ left: 100, right: tabsRight, bottom: 260 });
    let resultsTop = 288;
    app.node('[data-catalog-results]').getBoundingClientRect = () => ({ top: resultsTop });
    app.node('[data-catalog-facets]').getBoundingClientRect = () => ({ left: 920, top: 200 + parseFloat(app.catalog.style['--catalog-filter-top']), width: 180 });
    let buttonLeft = 200;
    const button = {
        dataset: { catalogCategory: 'hinges' },
        closest() { return this; },
        getBoundingClientRect: () => ({ left: buttonLeft, width: 100, bottom: 252 }),
    };
    app.handlers.get('catalog:click')({ button: 0, target: button, preventDefault() {} });
    assert.equal(app.catalog.style['--catalog-filter-top'], '88px');
    assert.equal(connector.hidden, false);
    assert.equal(connector.style['--connector-start-x'], '150px');
    assert.equal(connector.style['--connector-start-y'], '52px');
    assert.equal(connector.style['--connector-start-height'], '10px');
    assert.equal(connector.style['--connector-lane'], '62px');
    assert.equal(200 + parseFloat(connector.style['--connector-lane']), (252 + 272) / 2);
    assert.ok(200 + parseFloat(connector.style['--connector-lane']) > 260);
    assert.ok(200 + parseFloat(connector.style['--connector-lane']) < 272);
    assert.equal(connector.style['--connector-width'], '760px');
    assert.equal(connector.style['--connector-end-height'], '26px');
    resultsTop = 312;
    await app.respond(0, { filters: 'HINGE FILTERS' });
    assert.equal(app.catalog.style['--catalog-filter-top'], '112px');
    assert.equal(app.catalog.style['--catalog-filter-height'], '576px');
    assert.equal(connector.style['--connector-lane'], '62px');
    assert.equal(connector.style['--connector-end-height'], '50px');
    app.node('[data-catalog-active]').getBoundingClientRect = () => ({ top: 276, height: 24 });
    app.handlers.get('window:resize')();
    assert.equal(connector.style['--connector-lane'], '62px');
    assert.ok(200 + parseFloat(connector.style['--connector-lane']) < 276);
    app.node('[data-catalog-active]').getBoundingClientRect = () => ({ top: 0, height: 0 });
    app.handlers.get('window:resize')();
    assert.equal(connector.style['--connector-lane'], '62px');
    resultsTop = 300;
    app.handlers.get('window:resize')();
    assert.equal(app.catalog.style['--catalog-filter-top'], '100px');
    button.dataset.catalogCategory = 'boards';
    buttonLeft = 1020;
    app.handlers.get('catalog:click')({ button: 0, target: button, preventDefault() {} });
    assert.equal(connector.style['--connector-left'], '910px');
    assert.equal(connector.style['--connector-width'], '60px');
    tabsRight = 1000;
    app.handlers.get('document:scroll')({ target: { matches: () => true } });
    assert.equal(connector.hidden, true);
    tabsRight = 1200;
    app.handlers.get('document:scroll')({ target: { matches: () => true } });
    assert.equal(connector.hidden, false);
    desktop = false;
    app.handlers.get('window:resize')();
    assert.equal(connector.hidden, true);
    desktop = true;
    app.handlers.get('window:resize')();
    assert.equal(connector.hidden, false);
    app.handlers.get('document:keydown')({ key: 'Escape' });
    assert.equal(connector.hidden, true);
    await app.respond(1, { filters: 'BOARD FILTERS' });
    assert.equal(connector.hidden, true);
});

test('category scroller hides its scrollbar, exposes edges and drags without selecting a category', () => {
    const app = setup();
    const tabs = app.node('.shop-category-tabs');
    const scroller = app.node('[data-category-scroller]');
    const start = app.node('[data-category-scroll-start]');
    const end = app.node('[data-category-scroll-end]');
    tabs.clientWidth = 300;
    tabs.scrollWidth = 900;
    tabs.scrollBy = ({ left }) => { tabs.scrollLeft += left; };
    app.handlers.get('window:resize')();
    assert.equal(scroller.dataset.categoryOverflow, 'true');
    assert.equal(scroller.dataset.categoryStart, 'true');
    assert.equal(scroller.dataset.categoryEnd, 'false');
    assert.equal(start.disabled, true);
    assert.equal(end.disabled, false);
    assert.equal(Number(scroller.style['--category-start-tint']), 0);
    assert.equal(Number(scroller.style['--category-end-tint']), 1);
    app.handlers.get('[data-category-scroll-end]:click')();
    assert.equal(tabs.scrollLeft, 64);
    app.handlers.get('.shop-category-tabs:scroll')();
    assert.equal(scroller.dataset.categoryStart, 'false');
    assert.equal(start.disabled, false);
    assert.equal(Number(scroller.style['--category-end-tint']), 1 - 64 / 600);
    assert.equal(Number(scroller.style['--category-start-tint']), 64 / 600);
    for (const progress of [0.25, 0.5, 0.75]) {
        tabs.scrollLeft = 600 * progress;
        app.handlers.get('.shop-category-tabs:scroll')();
        assert.equal(Number(scroller.style['--category-start-tint']), progress);
        assert.equal(Number(scroller.style['--category-end-tint']), 1 - progress);
    }
    tabs.scrollLeft = 300;
    app.handlers.get('.shop-category-tabs:scroll')();
    assert.equal(scroller.style['--category-start-tint'], scroller.style['--category-end-tint']);
    tabs.scrollLeft = 600;
    app.handlers.get('.shop-category-tabs:scroll')();
    assert.equal(scroller.dataset.categoryEnd, 'true');
    assert.equal(end.disabled, true);
    assert.equal(Number(scroller.style['--category-start-tint']), 1);
    assert.equal(Number(scroller.style['--category-end-tint']), 0);
    let dragStartPrevented = false;
    app.handlers.get('.shop-category-tabs:dragstart')({ preventDefault() { dragStartPrevented = true; } });
    assert.equal(dragStartPrevented, true);
    let pointerDownPrevented = false;
    app.handlers.get('.shop-category-tabs:pointerdown')({ pointerType: 'mouse', button: 0, pointerId: 1, clientX: 220, preventDefault() { pointerDownPrevented = true; } });
    assert.equal(pointerDownPrevented, false);
    app.handlers.get('.shop-category-tabs:pointermove')({ pointerId: 1, clientX: 120, preventDefault() {} });
    assert.equal(tabs.scrollLeft, 700);
    app.handlers.get('.shop-category-tabs:pointerup')({ pointerId: 1 });
    let prevented = false;
    const category = { closest() { return category; }, dataset: { catalogCategory: 'hinges' } };
    app.handlers.get('catalog:click')({ button: 0, target: category, preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    assert.equal(app.form.elements.category.value, '');
    tabs.scrollWidth = 300;
    tabs.scrollLeft = 0;
    app.handlers.get('window:resize')();
    assert.equal(scroller.dataset.categoryOverflow, 'false');
    assert.equal(start.disabled, true);
    assert.equal(end.disabled, true);
});

test('scrolling a selected category out of either edge resets it even with its panel closed', async () => {
    for (const edge of ['left', 'right']) {
        for (const panelHidden of [false, true]) {
            let left = 200;
            const selected = {
                dataset: { catalogCategory: 'hinges' },
                attributes: {},
                setAttribute(name, value) { this.attributes[name] = value; },
                removeAttribute(name) { delete this.attributes[name]; },
                getBoundingClientRect: () => ({ left, right: left + 100, width: 100 }),
            };
            const app = setup({ category: 'hinges', buttons: [selected], href: 'http://localhost/eshop/katalog?category=hinges&page=3&filters[1][]=Blum' });
            const tabs = app.node('.shop-category-tabs');
            tabs.getBoundingClientRect = () => ({ left: 100, right: 400, width: 300 });
            const panel = app.node('[data-catalog-facets]');
            panel.hidden = panelHidden;
            app.form.elements.q.value = 'Blum';
            app.form.elements.sort.value = 'price_desc';
            app.form.filterEntries = [['filters[1][]', 'Blum']];
            app.handlers.get('form:submit')({ preventDefault() {} });
            app.handlers.get('[data-catalog-path]:pointerenter')({ pointerType: 'mouse' });
            app.node('[data-catalog-active]').innerHTML = 'OLD FILTERS';
            left = edge === 'left' ? 1 : 399;
            app.handlers.get('.shop-category-tabs:scroll')();
            assert.equal(app.form.elements.category.value, 'hinges');
            assert.equal(app.pending.length, 1);
            left = edge === 'left' ? 0 : 400;
            app.handlers.get('.shop-category-tabs:scroll')();
            assert.equal(app.form.elements.category.value, '');
            assert.equal(selected.attributes['aria-current'], undefined);
            assert.equal(selected.attributes['aria-expanded'], 'false');
            assert.equal(panel.hidden, true);
            assert.equal(app.node('[data-category-connector]').hidden, true);
            assert.equal(app.node('[data-catalog-path]').open, false);
            assert.equal(app.node('[data-catalog-active]').innerHTML, '');
            assert.equal(app.pending.length, 2);
            assert.equal(app.pending[0].options.signal.aborted, true);
            assert.equal(app.pending[1].url.search, '?q=Blum&sort=price_desc');
            app.handlers.get('.shop-category-tabs:scroll')();
            assert.equal(app.pending.length, 2);
            await app.respond(1, { results: 'ALL CATEGORIES', filters: '', active: 'SEARCH ONLY' });
            await app.respond(0, { results: 'OLD CATEGORY', filters: 'OLD FILTERS' });
            assert.equal(app.node('[data-catalog-results]').innerHTML, 'ALL CATEGORIES');
            assert.equal(panel.hidden, true);
            assert.equal(panel.innerHTML, '');
            assert.equal(new URL(app.location.href).search, '?q=Blum&sort=price_desc');
        }
    }
});

test('holding arrows scrolls continuously in both directions and stops without an extra click step', () => {
    const app = setup();
    const tabs = app.node('.shop-category-tabs');
    tabs.clientWidth = 300;
    tabs.scrollWidth = 900;
    const steps = [];
    tabs.scrollBy = options => steps.push(options);
    app.handlers.get('window:resize')();
    const press = direction => app.handlers.get(`[data-category-scroll-${direction}]:pointerdown`)({ button: 0 });
    press('end');
    app.frame(0);
    app.frame(20);
    assert.equal(tabs.scrollLeft, 6);
    app.frame(40);
    assert.equal(tabs.scrollLeft, 12);
    app.handlers.get('window:pointerup')();
    assert.equal(app.frames.size, 0);
    app.handlers.get('[data-category-scroll-end]:click')({ detail: 1 });
    assert.equal(steps.length, 0);
    press('start');
    app.frame(60);
    app.frame(80);
    assert.equal(tabs.scrollLeft, 6);
    app.frame(100);
    assert.equal(tabs.scrollLeft, 0);
    assert.equal(app.frames.size, 0);
    for (const stopEvent of ['[data-category-scroll-end]:pointerleave', 'window:pointercancel', 'window:blur']) {
        press('end');
        assert.equal(app.frames.size, 1);
        app.handlers.get(stopEvent)();
        assert.equal(app.frames.size, 0);
    }
    press('end');
    app.handlers.get('window:pointerup')();
    app.handlers.get('[data-category-scroll-end]:click')({ detail: 1 });
    assert.equal(steps[0].left, 64);
    assert.equal(steps[0].behavior, 'smooth');
    assert.equal(app.pending.length, 0);
});

test('double clicking category arrows scrolls to the corresponding edge and stops holding', () => {
    const app = setup();
    const tabs = app.node('.shop-category-tabs');
    tabs.clientWidth = 300;
    tabs.scrollWidth = 1500;
    const jumps = [];
    tabs.scrollBy = ({ left }) => { tabs.scrollLeft += left; };
    tabs.scrollTo = options => { jumps.push(options); tabs.scrollLeft = options.left; };
    for (const direction of ['start', 'end']) {
        tabs.scrollLeft = 600;
        app.handlers.get('window:resize')();
        const prefix = `[data-category-scroll-${direction}]`;
        app.handlers.get(`${prefix}:click`)({ detail: 1 });
        app.handlers.get(`${prefix}:click`)({ detail: 2 });
        app.handlers.get(`${prefix}:pointerdown`)({ button: 0 });
        assert.equal(app.frames.size, 1);
        let prevented = false;
        app.handlers.get(`${prefix}:dblclick`)({ preventDefault() { prevented = true; } });
        assert.equal(prevented, true);
        assert.equal(app.frames.size, 0);
        assert.equal(tabs.scrollLeft, direction === 'start' ? 0 : 1200);
        assert.equal(jumps.at(-1).behavior, 'smooth');
        app.handlers.get('.shop-category-tabs:scroll')();
        assert.equal(app.node(prefix).disabled, true);
    }
    assert.equal(jumps.length, 2);
    assert.equal(app.pending.length, 0);
});

test('category page arrows keep half an edge category visible and clamp to both ends', () => {
    const app = setup();
    const tabs = app.node('.shop-category-tabs');
    tabs.clientWidth = 340;
    tabs.scrollWidth = 1100;
    tabs.getBoundingClientRect = () => ({ left: 100, right: 440, width: 340 });
    const jumps = [];
    tabs.scrollTo = options => { jumps.push(options); tabs.scrollLeft = options.left; };
    let offset = 0;
    for (const width of [80, 140, 100, 200, 90, 260, 170]) {
        const start = offset;
        app.categoryButtons.push({ getBoundingClientRect: () => ({
            left: 100 + start - tabs.scrollLeft,
            right: 100 + start + width - tabs.scrollLeft,
            width,
        }) });
        offset += width + 10;
    }
    const page = direction => {
        app.handlers.get('window:resize')();
        app.handlers.get(`[data-category-page-${direction}]:click`)();
        app.handlers.get('.shop-category-tabs:scroll')();
    };
    page('start');
    assert.equal(jumps.length, 0);
    page('end');
    assert.equal(tabs.scrollLeft, 290);
    const previous = app.categoryButtons[2].getBoundingClientRect();
    assert.equal(previous.right - 100, previous.width / 2);
    assert.equal(jumps.at(-1).behavior, 'smooth');
    tabs.scrollLeft = 400;
    page('start');
    assert.equal(tabs.scrollLeft, 265);
    const following = app.categoryButtons[4].getBoundingClientRect();
    assert.equal(440 - following.left, following.width / 2);
    tabs.scrollLeft = 750;
    page('end');
    assert.equal(tabs.scrollLeft, 760);
    assert.equal(app.node('[data-category-page-end]').disabled, true);
    tabs.scrollLeft = 10;
    page('start');
    assert.equal(tabs.scrollLeft, 0);
    assert.equal(app.node('[data-category-page-start]').disabled, true);
    app.categoryButtons.length = 0;
    app.categoryButtons.push({ getBoundingClientRect: () => ({ left: -100, right: 900, width: 1000 }) });
    page('end');
    assert.equal(tabs.scrollLeft, 170);
    assert.equal(app.pending.length, 0);
});

test('hover switches empty categories even after clicking, and a short hover or Escape cancels opening', () => {
    const app = setup();
    const button = category => {
        const link = { dataset: { catalogCategory: category }, contains: () => false };
        link.closest = () => link;
        return link;
    };
    const hinges = button('hinges');
    const boards = button('boards');
    const all = button('');
    const hover = target => app.handlers.get('catalog:pointerover')({ pointerType: 'mouse', target });
    const leave = target => app.handlers.get('catalog:pointerout')({ pointerType: 'mouse', target, relatedTarget: null });
    const tick = () => { for (const callback of [...app.timers.values()]) callback(); };
    hover(hinges);
    leave(hinges);
    tick();
    assert.equal(app.pending.length, 0);
    hover(hinges);
    app.handlers.get('document:keydown')({ key: 'Escape' });
    assert.equal(app.timers.size, 0);
    hover(hinges);
    tick();
    leave(hinges);
    hover(boards);
    assert.equal(app.form.elements.category.value, 'hinges');
    assert.equal(app.timerDelays.get([...app.timers.keys()][0]), 400);
    tick();
    assert.equal(app.form.elements.category.value, 'boards');
    assert.equal(app.node('[data-catalog-facets]').hidden, false);
    leave(boards);
    hover(all);
    tick();
    assert.equal(app.form.elements.category.value, '');
    assert.equal(app.node('[data-catalog-facets]').hidden, true);
    app.handlers.get('catalog:click')({ button: 0, target: hinges, preventDefault() {} });
    hover(boards);
    tick();
    assert.equal(app.form.elements.category.value, 'boards');
    assert.equal(app.timers.size, 0);
});

test('selected choices and numeric ranges block category hover but allow explicit clicks', () => {
    for (const input of [
        { name: 'filters[1][]', type: 'checkbox', value: 'Blum', checked: true },
        { name: 'filters[2][]', type: 'checkbox', value: '0', checked: true },
        { name: 'filters[3][min]', type: 'number', value: '0' },
        { name: 'filters[3][max]', type: 'number', value: '100' },
    ]) {
        for (const targetCategory of ['boards', '']) {
            const app = setup({ category: 'hinges' });
            const panel = app.node('[data-catalog-facets]');
            panel.inputs = [input];
            panel.innerHTML = 'SELECTED FILTERS';
            const button = category => ({ dataset: { catalogCategory: category }, contains: () => false, closest() { return this; } });
            const selected = button('hinges');
            const other = button(targetCategory);
            const hover = target => {
                app.handlers.get('catalog:pointerover')({ pointerType: 'mouse', target });
                for (const callback of [...app.timers.values()]) callback();
            };
            hover(selected);
            assert.equal(panel.hidden, false);
            hover(other);
            assert.equal(app.form.elements.category.value, 'hinges');
            assert.equal(panel.innerHTML, 'SELECTED FILTERS');
            assert.equal(panel.hidden, false);
            assert.equal(app.pending.length, 0);
            assert.equal(app.historyUrls.length, 0);
            app.handlers.get('document:click')({ target: {} });
            hover(other);
            assert.equal(app.form.elements.category.value, 'hinges');
            assert.equal(panel.hidden, true);
            app.handlers.get('catalog:click')({ button: 0, target: other, preventDefault() {} });
            assert.equal(app.form.elements.category.value, targetCategory);
            assert.equal(panel.innerHTML, '');
            assert.equal(app.pending.length, 1);
        }
    }
});

test('hover checks the current selection when its timer fires and resumes after clearing filters', () => {
    const app = setup({ category: 'hinges' });
    const selected = { dataset: { catalogCategory: 'hinges' }, closest() { return this; } };
    app.handlers.get('catalog:click')({ button: 0, target: selected, preventDefault() {} });
    const input = { name: 'filters[1][]', type: 'checkbox', value: 'Blum', checked: false };
    app.node('[data-catalog-facets]').inputs = [
        input,
        { name: 'filters[2][min]', type: 'number', value: '' },
        { type: 'search', value: 'local option search' },
    ];
    app.form.elements.q.value = 'Panel';
    const other = { dataset: { catalogCategory: 'boards' }, contains: () => false, closest() { return this; } };
    app.handlers.get('catalog:pointerover')({ pointerType: 'mouse', target: other });
    input.checked = true;
    for (const callback of [...app.timers.values()]) callback();
    assert.equal(app.form.elements.category.value, 'hinges');
    assert.equal(app.pending.length, 0);
    input.checked = false;
    app.handlers.get('catalog:pointerover')({ pointerType: 'mouse', target: other });
    for (const callback of [...app.timers.values()]) callback();
    assert.equal(app.form.elements.category.value, 'boards');
    assert.equal(app.form.elements.q.value, 'Panel');
    assert.equal(app.pending.length, 1);
});

test('clicking the active category clears its search and filters then closes its panel', async () => {
    const app = setup({ href: 'http://localhost/eshop/katalog?category=hinges&q=Blum&sort=price_desc&filters[1][]=Blum', category: 'hinges' });
    const panel = app.node('[data-catalog-facets]');
    const path = app.node('[data-catalog-path]');
    const active = app.node('[data-catalog-active]');
    const input = { name: 'filters[1][]', type: 'checkbox', value: 'Blum', checked: true };
    panel.hidden = false;
    panel.inputs = [input];
    app.form.elements.q.value = 'Blum';
    app.form.elements.sort.value = 'price_desc';
    app.handlers.get('[data-catalog-path]:pointerenter')({ pointerType: 'mouse' });
    const category = { dataset: { catalogCategory: 'hinges' }, contains: () => false, closest() { return this; } };
    app.handlers.get('catalog:click')({ button: 0, target: category, preventDefault() {} });
    assert.equal(panel.hidden, true);
    assert.equal(path.open, false);
    assert.equal(active.hidden, true);
    assert.equal(input.checked, false);
    assert.equal(app.form.elements.q.value, '');
    assert.equal(app.form.elements.category.value, 'hinges');
    assert.equal(app.form.elements.sort.value, 'price_desc');
    assert.equal(app.pending.length, 1);
    assert.equal(app.pending[0].url.searchParams.get('category'), 'hinges');
    assert.equal(app.pending[0].url.searchParams.get('sort'), 'price_desc');
    assert.equal(app.pending[0].url.searchParams.has('q'), false);
    assert.equal([...app.pending[0].url.searchParams.keys()].some(name => name.startsWith('filters[')), false);
    await app.respond(0, { active: '', filters: 'RESET FILTERS' });
    assert.equal(panel.hidden, true);
    assert.equal(active.innerHTML, '');
});

test('hover waits 400ms, leaves category open and keeps All without a panel', () => {
    for (const category of ['', 'hinges']) {
        const app = setup();
        const button = { dataset: { catalogCategory: category }, contains: () => false };
        button.closest = () => button;
        const event = { pointerType: 'mouse', target: button };
        const panel = app.node('[data-catalog-facets]');
        app.handlers.get('catalog:pointerover')(event);
        assert.equal(panel.hidden, true);
        assert.equal(app.pending.length, 0);
        assert.equal(app.timerDelays.get([...app.timers.keys()][0]), 400);
        for (const callback of [...app.timers.values()]) callback();
        assert.equal(panel.hidden, category === '');
        assert.equal(app.form.elements.category.value, category);
        app.handlers.get('catalog:pointerout')({ ...event, relatedTarget: null });
        assert.equal(app.timers.size, 0);
        assert.equal(panel.hidden, category === '');
        app.handlers.get('document:click')({ target: {} });
        assert.equal(panel.hidden, true);
    }
});

test('returning from a product opens and pins its category without refetching or changing selections', async () => {
    const button = {
        dataset: { catalogCategory: 'hinges' }, attributes: {},
        setAttribute(key, value) { this.attributes[key] = value; },
        removeAttribute(key) { delete this.attributes[key]; },
        closest() { return this; },
    };
    const app = setup({ href: 'http://localhost/eshop/katalog?category=hinges&filters[1][]=Blum#catalog-product-42', category: 'hinges', buttons: [button] });
    const panel = app.node('[data-catalog-facets]');
    assert.equal(panel.hidden, false);
    assert.equal(button.attributes['aria-expanded'], 'true');
    assert.equal(app.pending.length, 0);
    const input = { name: 'filters[1][]', type: 'checkbox', value: 'Blum', checked: true };
    panel.inputs = [input];
    app.node('[data-category-bar]').contains = target => target === input;
    app.handlers.get('window:pageshow')();
    app.handlers.get('catalog:pointerout')({ pointerType: 'mouse', target: button, relatedTarget: null });
    app.handlers.get('document:click')({ target: input });
    assert.equal(app.timers.size, 0);
    assert.equal(panel.hidden, false);
    assert.equal(input.checked, true);
    app.handlers.get('document:click')({ target: {} });
    assert.equal(panel.hidden, true);
    app.handlers.get('window:popstate')();
    assert.equal(panel.hidden, false);
    assert.equal(input.checked, true);
    assert.equal(app.pending.length, 1);
    await app.respond(0, { filters: 'RETURNED FILTERS' });
    app.handlers.get('catalog:pointerout')({ pointerType: 'mouse', target: button, relatedTarget: null });
    assert.equal(app.timers.size, 0);
    assert.equal(app.historyUrls.length, 0);
    app.handlers.get('document:keydown')({ key: 'Escape' });
    assert.equal(panel.hidden, true);
});

test('ordinary catalog visits and returns without a category do not open filters', () => {
    for (const [href, category] of [
        ['http://localhost/eshop/katalog?category=hinges', 'hinges'],
        ['http://localhost/eshop/katalog#catalog-product-42', ''],
        ['http://localhost/eshop/katalog?category=unknown#catalog-product-42', 'unknown'],
    ]) {
        const app = setup({ href, category });
        app.handlers.get('window:pageshow')();
        assert.equal(app.node('[data-catalog-facets]').hidden, true);
        assert.equal(app.pending.length, 0);
    }
});

test('assortment trigger aligns with the category title on resize and scroll', () => {
    const app = setup();
    let titleTop = 220;
    app.node('.shop-catalog-heading').getBoundingClientRect = () => ({ top: 100 - app.window.scrollY });
    app.node('.shop-category-title').getBoundingClientRect = () => ({ top: titleTop - app.window.scrollY, width: 80 });
    app.handlers.get('window:resize')();
    assert.equal(app.catalog.style['--catalog-assortment-top'], '120px');
    app.window.scrollY = 60;
    app.handlers.get('window:scroll')();
    assert.equal(app.catalog.style['--catalog-assortment-top'], '120px');
    titleTop = 240;
    app.handlers.get('window:resize')();
    assert.equal(app.catalog.style['--catalog-assortment-top'], '140px');
});

test('product row height fits six rows and stays stable while scrolling', async () => {
    const app = setup();
    let listTop = 300;
    app.node('.shop-product-list').getBoundingClientRect = () => ({ top: listTop - app.window.scrollY });
    const rowHeight = () => parseFloat(app.catalog.style['--catalog-product-row-height']);
    app.handlers.get('window:resize')();
    assert.equal(rowHeight(), (900 - 300 - 8) / 6);
    app.window.scrollY = 200;
    app.handlers.get('window:scroll')();
    assert.equal(rowHeight(), (900 - 300 - 8) / 6);
    app.window.innerHeight = 1080;
    app.handlers.get('window:resize')();
    assert.equal(rowHeight(), (1080 - 300 - 8) / 6);
    listTop = 330;
    app.handlers.get('form:submit')({ preventDefault() {} });
    await app.respond(0, { results: 'UPDATED PRODUCTS' });
    assert.equal(rowHeight(), (1080 - 330 - 8) / 6);
    listTop = 1200;
    app.handlers.get('window:resize')();
    assert.equal(rowHeight(), 0);
});

test('filter height follows resize and content updates without pushing the footer down on scroll', async () => {
    const app = setup();
    app.catalog.getBoundingClientRect = () => ({ top: 80 - app.window.scrollY });
    assert.equal(app.catalog.style['--catalog-min-height'], '820px');
    const button = {
        dataset: { catalogCategory: 'hinges' },
        contains: () => false,
        closest: () => button,
    };
    let top = 240;
    app.node('[data-catalog-facets]').getBoundingClientRect = () => ({ top });
    app.handlers.get('catalog:pointerover')({ pointerType: 'mouse', target: button });
    for (const callback of [...app.timers.values()]) callback();
    assert.equal(app.catalog.style['--catalog-filter-height'], '648px');
    app.window.innerHeight = 700;
    app.handlers.get('window:resize')();
    assert.equal(app.catalog.style['--catalog-filter-height'], '448px');
    top = 140;
    app.window.scrollY = 100;
    app.handlers.get('window:scroll')();
    assert.equal(app.catalog.style['--catalog-filter-height'], '448px');
    assert.equal(app.catalog.style['--catalog-min-height'], '620px');
    top = -760;
    app.window.scrollY = 1000;
    app.handlers.get('window:scroll')();
    assert.equal(app.catalog.style['--catalog-filter-height'], '448px');
    assert.equal(app.catalog.style['--catalog-min-height'], '620px');
    app.window.scrollY = 0;
    top = 160;
    await app.respond(0, { filters: 'HINGE FILTERS' });
    assert.equal(app.catalog.style['--catalog-filter-height'], '528px');
    app.window.visualViewport = { offsetTop: 30, height: 400 };
    app.handlers.get('window:resize')();
    assert.equal(app.catalog.style['--catalog-filter-height'], '258px');
    top = 600;
    app.handlers.get('window:resize')();
    assert.equal(app.catalog.style['--catalog-filter-height'], '0px');
    app.handlers.get('document:keydown')({ key: 'Escape' });
    top = 100;
    app.handlers.get('window:resize')();
    assert.equal(app.catalog.style['--catalog-filter-height'], '0px');
});

test('mouse hover pins the panel after the delay and leaving the panel does not close it', () => {
    const app = setup();
    const button = {
        dataset: { catalogCategory: 'hinges' },
        getBoundingClientRect: () => ({ left: 0, bottom: 44 }),
        contains: () => false,
        closest: () => button,
    };
    const panel = app.node('[data-catalog-facets]');
    const input = { closest: () => null };
    panel.contains = target => target === input;
    app.handlers.get('catalog:pointerover')({ pointerType: 'touch', target: button });
    assert.equal(panel.hidden, true);
    app.handlers.get('catalog:pointerover')({ pointerType: 'mouse', target: button });
    assert.equal(panel.hidden, true);
    for (const callback of [...app.timers.values()]) callback();
    assert.equal(panel.hidden, false);
    assert.equal(app.pending.length, 1);
    assert.equal(app.timers.size, 0);
    app.handlers.get('catalog:pointerout')({ pointerType: 'mouse', target: button, relatedTarget: input });
    assert.equal(app.timers.size, 0);
    app.handlers.get('catalog:pointerout')({ pointerType: 'mouse', target: input, relatedTarget: null });
    assert.equal(panel.hidden, false);
    assert.equal(app.timers.size, 0);
    app.handlers.get('catalog:pointerover')({ pointerType: 'mouse', target: input });
    assert.equal(app.timers.size, 0);
    app.handlers.get('catalog:pointerout')({ pointerType: 'mouse', target: input, relatedTarget: null });
    for (const callback of [...app.timers.values()]) callback();
    assert.equal(panel.hidden, false);
    app.handlers.get('document:keydown')({ key: 'Escape' });
    assert.equal(panel.hidden, true);
});

test('clicking the active category toggles its empty panel without refetching', async () => {
    const app = setup();
    const button = {
        dataset: { catalogCategory: 'hinges' },
        contains: () => false,
        closest: () => button,
    };
    const panel = app.node('[data-catalog-facets]');
    const hover = { pointerType: 'mouse', target: button };
    app.handlers.get('catalog:pointerover')(hover);
    for (const callback of [...app.timers.values()]) callback();
    await app.respond(0, { filters: 'HINGE FILTERS' });
    app.handlers.get('catalog:pointerout')({ ...hover, relatedTarget: null });
    assert.equal(app.timers.size, 0);
    app.handlers.get('catalog:pointerover')(hover);
    assert.equal(app.timers.size, 0);
    assert.equal(app.pending.length, 1);
    app.handlers.get('catalog:click')({ ...hover, button: 0, detail: 1, preventDefault() {} });
    assert.equal(panel.hidden, true);
    assert.equal(app.node('[data-category-connector]').hidden, true);
    assert.equal(app.form.elements.category.value, 'hinges');
    assert.equal(app.pending.length, 1);
    app.handlers.get('catalog:click')({ ...hover, button: 0, detail: 0, preventDefault() {} });
    assert.equal(panel.hidden, false);
    assert.equal(app.pending.length, 1);
    app.handlers.get('catalog:click')({ ...hover, button: 0, detail: 0, preventDefault() {} });
    assert.equal(panel.hidden, true);
    assert.equal(app.pending.length, 1);
    app.handlers.get('catalog:click')({ ...hover, button: 0, detail: 1, preventDefault() {} });
    assert.equal(panel.hidden, false);
    app.handlers.get('document:keydown')({ key: 'Escape' });
    assert.equal(panel.hidden, true);
});

test('click opens immediately, cancels pending hover and allows another hover when filters are empty', async () => {
    const app = setup();
    const button = { dataset: { catalogCategory: 'hinges' }, contains: () => false };
    button.closest = () => button;
    const other = { dataset: { catalogCategory: 'boards' }, contains: () => false };
    other.closest = () => other;
    const panel = app.node('[data-catalog-facets]');
    const input = { closest: () => null };
    panel.contains = target => target === input;
    app.node('[data-category-bar]').contains = target => [button, other, input].includes(target);
    const hover = { pointerType: 'mouse', target: button };
    app.handlers.get('catalog:pointerover')(hover);
    assert.equal(app.timers.size, 1);
    app.handlers.get('catalog:click')({ ...hover, button: 0, detail: 1, preventDefault() {} });
    await app.respond(0, { filters: 'HINGE FILTERS' });
    app.handlers.get('document:click')({ target: button });
    assert.equal(app.timers.size, 0);
    app.handlers.get('catalog:pointerout')({ ...hover, relatedTarget: null });
    app.handlers.get('catalog:pointerout')({ pointerType: 'mouse', target: input, relatedTarget: null });
    app.handlers.get('document:scroll')({ target: { matches: () => true } });
    app.handlers.get('document:click')({ target: input });
    assert.equal(panel.hidden, false);
    assert.equal(app.timers.size, 0);
    assert.equal(app.form.elements.category.value, 'hinges');
    assert.equal(app.pending.length, 1);
    app.handlers.get('catalog:pointerover')({ pointerType: 'mouse', target: other });
    assert.equal(app.timers.size, 1);
    assert.equal(app.timerDelays.get([...app.timers.keys()][0]), 400);
    for (const callback of [...app.timers.values()]) callback();
    assert.equal(app.form.elements.category.value, 'boards');
    assert.equal(panel.hidden, false);
    assert.equal(app.pending.length, 2);
    app.handlers.get('document:click')({ target: {} });
    assert.equal(panel.hidden, true);
    app.handlers.get('catalog:pointerover')(hover);
    assert.equal(app.timers.size, 1);
    assert.equal(app.timerDelays.get([...app.timers.keys()][0]), 400);
    app.handlers.get('catalog:pointerout')({ ...hover, relatedTarget: null });
    assert.equal(app.timers.size, 0);
    for (const callback of [...app.timers.values()]) callback();
    assert.equal(panel.hidden, true);
});

test('touch and keyboard clicks pin categories and All clears pinning', async () => {
    const app = setup();
    const panel = app.node('[data-catalog-facets]');
    const button = { dataset: { catalogCategory: 'hinges' }, contains: () => false };
    button.closest = () => button;
    const other = { dataset: { catalogCategory: 'boards' }, contains: () => false };
    other.closest = () => other;
    const click = { button: 0, preventDefault() {} };
    app.handlers.get('catalog:click')({ ...click, pointerType: 'touch', detail: 1, target: button });
    await app.respond(0, { filters: 'HINGE FILTERS' });
    app.handlers.get('catalog:pointerout')({ pointerType: 'mouse', target: button, relatedTarget: null });
    assert.equal(app.timers.size, 0);
    app.handlers.get('catalog:click')({ ...click, detail: 0, target: other });
    assert.equal(app.form.elements.category.value, 'boards');
    await app.respond(1, { filters: 'BOARD FILTERS' });
    app.handlers.get('catalog:pointerout')({ pointerType: 'mouse', target: other, relatedTarget: null });
    assert.equal(app.timers.size, 0);
    assert.equal(panel.hidden, false);
    app.handlers.get('document:keydown')({ key: 'Escape' });
    assert.equal(panel.hidden, true);
    app.handlers.get('catalog:pointerover')({ pointerType: 'mouse', target: other });
    app.handlers.get('catalog:pointerout')({ pointerType: 'mouse', target: other, relatedTarget: null });
    assert.equal(app.timers.size, 0);
    app.handlers.get('catalog:click')({ ...click, target: other });
    const all = { dataset: { catalogCategory: '' } };
    all.closest = () => all;
    app.handlers.get('catalog:click')({ ...click, target: all });
    assert.equal(panel.hidden, true);
    assert.equal(app.form.elements.category.value, '');
    app.handlers.get('catalog:pointerover')({ pointerType: 'mouse', target: button });
    for (const callback of [...app.timers.values()]) callback();
    assert.equal(panel.hidden, false);
    app.handlers.get('catalog:pointerout')({ pointerType: 'mouse', target: button, relatedTarget: null });
    assert.equal(app.timers.size, 0);
});

test('a delayed response cannot reopen a panel after an outside click', async () => {
    const app = setup();
    const button = {
        dataset: { catalogCategory: 'hinges' },
        contains: () => false,
        closest: () => button,
    };
    app.handlers.get('catalog:pointerover')({ pointerType: 'mouse', target: button });
    for (const callback of [...app.timers.values()]) callback();
    app.handlers.get('document:click')({ target: {} });
    await app.respond(0, { filters: 'HINGE FILTERS' });
    assert.equal(app.node('[data-catalog-facets]').hidden, true);
});

test('typing is debounced and an older response cannot overwrite a newer query', async () => {
    const app = setup();
    app.handlers.get('form:submit')({ preventDefault() {} });
    assert.equal(app.pending[0].options.cache, 'no-store');
    assert.equal(app.pending[0].options.headers.Accept, 'application/json');
    app.form.elements.q.value = 'Blum';
    app.handlers.get('form:input')({ target: { matches: selector => selector.includes('input[type="search"]'), closest: () => null } });
    assert.equal(app.pending.length, 1);
    assert.equal(app.pending[0].options.signal.aborted, true);
    await app.respond(0, { results: 'OLD' });
    assert.notEqual(app.node('[data-catalog-results]').innerHTML, 'OLD');
    for (const callback of app.timers.values()) callback();
    assert.equal(app.pending.length, 2);
    assert.equal(app.pending[1].url.searchParams.get('q'), 'Blum');
    await app.respond(1, { results: 'NEW', total: 4 });
    assert.equal(app.node('[data-catalog-results]').innerHTML, 'NEW');
    assert.equal(app.node('[data-catalog-results]').attributes['aria-busy'], 'false');
    assert.equal(app.historyUrls.length, 1);
    assert.deepEqual(app.windowEvents, ['catalog:historychange']);
});

test('checkbox filters update immediately and preserve multiple values', async () => {
    const app = setup();
    app.form.filterEntries = [['filters[1][]', 'Blum'], ['filters[1][]', 'Hettich']];
    app.handlers.get('form:change')({ target: { name: 'filters[1][]', matches: () => true, closest: () => null } });
    assert.equal(app.pending.length, 1);
    assert.deepEqual(app.pending[0].url.searchParams.getAll('filters[1][]'), ['Blum', 'Hettich']);
    await app.respond(0, { total: 2 });
    assert.equal(app.node('[data-catalog-total]').textContent, '2 položiek');
});

test('changing category replaces facets without applying old category controls', async () => {
    const app = setup();
    app.node('[data-catalog-facets]').innerHTML = 'OLD FILTERS';
    app.form.elements.category.value = 'boards';
    app.handlers.get('form:change')({ target: { name: 'category', matches: () => true, closest: () => null } });
    assert.equal(app.node('[data-catalog-facets]').innerHTML, '');
    assert.equal(app.pending[0].url.searchParams.get('category'), 'boards');
    await app.respond(0, { filters: 'BOARD FILTERS' });
    assert.equal(app.node('[data-catalog-facets]').innerHTML, 'BOARD FILTERS');
});

test('validation failure is visible and retains previous results for retry', async () => {
    const app = setup();
    app.node('[data-catalog-results]').innerHTML = 'CURRENT';
    app.handlers.get('form:submit')({ preventDefault() {} });
    await app.respond(0, { errors: { range: ['Invalid range'] } }, false);
    assert.equal(app.node('[data-catalog-error]').hidden, false);
    assert.equal(app.node('[data-catalog-error]').textContent, 'Invalid range');
    assert.equal(app.node('[data-catalog-results]').innerHTML, 'CURRENT');
    assert.equal(app.node('[data-catalog-results]').attributes['aria-busy'], 'false');
    app.handlers.get('form:submit')({ preventDefault() {} });
    await app.respond(1, { results: 'RETRIED' });
    assert.equal(app.node('[data-catalog-error]').hidden, true);
    assert.equal(app.node('[data-catalog-results]').innerHTML, 'RETRIED');
});

test('browser back restores controls and results without adding history', async () => {
    const app = setup();
    app.location.href = 'http://localhost/eshop/katalog?category=hinges&q=Blum&sort=price_asc';
    app.handlers.get('window:popstate')();
    assert.equal(app.form.elements.category.value, 'hinges');
    assert.equal(app.form.elements.q.value, 'Blum');
    assert.equal(app.form.elements.sort.value, 'price_asc');
    await app.respond(0, { results: 'RESTORED', filters: 'RESTORED FILTERS' });
    assert.equal(app.node('[data-catalog-results]').innerHTML, 'RESTORED');
    assert.equal(app.node('[data-catalog-facets]').innerHTML, 'RESTORED FILTERS');
    assert.equal(app.historyUrls.length, 0);
    assert.deepEqual(app.windowEvents, []);
});

test('pagination links load asynchronously and modifier clicks retain browser behavior', async () => {
    const app = setup();
    let prevented = false;
    const event = { button: 0, preventDefault() { prevented = true; }, target: { closest: () => ({ href: 'http://localhost/eshop/katalog?category=hinges&page=2' }) } };
    app.handlers.get('catalog:click')({ ...event, ctrlKey: true });
    assert.equal(app.pending.length, 0);
    app.handlers.get('catalog:click')(event);
    assert.equal(prevented, true);
    assert.equal(app.pending[0].url.searchParams.get('page'), '2');
    await app.respond(0, { results: 'PAGE 2' });
    assert.equal(app.node('[data-catalog-results]').innerHTML, 'PAGE 2');
});

test('removing one active filter immediately synchronizes remaining checkbox values', async () => {
    const app = setup();
    app.form.elements.category.value = 'hinges';
    const removed = { name: 'filters[12][]', type: 'checkbox', value: 'Blum', checked: true };
    const retained = { name: 'filters[12][]', type: 'checkbox', value: 'Hettich', checked: true };
    app.node('[data-catalog-facets]').inputs = [removed, retained];
    app.handlers.get('catalog:click')({ button: 0, preventDefault() {}, target: { closest: () => ({ href: 'http://localhost/eshop/katalog?category=hinges&filters[12][1]=Hettich' }) } });
    assert.equal(removed.checked, false);
    assert.equal(retained.checked, true);
    await app.respond(0, { total: 1 });
});

test('hovering and clicking filter path links keeps the category panel open through updates', async () => {
    const app = setup({ category: 'hinges' });
    const panel = app.node('[data-catalog-facets]');
    const path = app.node('[data-catalog-path]');
    const category = { dataset: { catalogCategory: 'hinges' }, closest() { return this; } };
    app.handlers.get('catalog:click')({ button: 0, target: category, preventDefault() {} });
    app.handlers.get('[data-catalog-path]:pointerenter')({ pointerType: 'mouse' });
    assert.equal(panel.hidden, false);
    const removed = { name: 'filters[12][]', type: 'checkbox', value: 'Blum', checked: true };
    const retained = { name: 'filters[12][]', type: 'checkbox', value: 'Hettich', checked: true };
    panel.inputs = [removed, retained];
    for (const [index, query] of ['&filters[12][1]=Hettich', ''].entries()) {
        const link = { href: `http://localhost/eshop/katalog?category=hinges${query}`, dataset: index === 1 ? { catalogClearFilters: '' } : {}, closest() { return this; } };
        const icon = { closest: () => link };
        path.contains = target => target === link || target === icon;
        app.handlers.get('catalog:click')({ button: 0, target: icon, preventDefault() {} });
        app.handlers.get('document:click')({ target: icon });
        assert.equal(panel.hidden, false);
        assert.equal(path.open, index === 0);
        if (index === 1) {
            assert.equal(path.attributes['data-path-hover'], undefined);
            assert.equal(app.node('[data-catalog-active]').style.minHeight, '');
            assert.equal(app.node('[data-catalog-active]').hidden, true);
        }
        assert.equal(removed.checked, false);
        assert.equal(retained.checked, index === 0);
        await app.respond(index, { filters: 'UPDATED FILTERS', total: 3 });
        assert.equal(panel.hidden, false);
        assert.equal(path.open, index === 0);
        assert.equal(app.node('[data-catalog-active]').hidden, false);
        assert.equal(app.form.elements.category.value, 'hinges');
    }
    app.handlers.get('document:click')({ target: {} });
    assert.equal(panel.hidden, true);
});

test('panel reset clears filters without closing the panel and hides the path immediately', async () => {
    const app = setup({ category: 'hinges' });
    const panel = app.node('[data-catalog-facets]');
    const path = app.node('[data-catalog-path]');
    const active = app.node('[data-catalog-active]');
    panel.hidden = false;
    panel.inputs = [{ name: 'filters[1][]', type: 'checkbox', value: 'Blum', checked: true }];
    const link = { dataset: { catalogClearFilters: '' }, href: 'http://localhost/eshop/katalog?category=hinges&sort=price_desc', closest() { return this; } };
    panel.contains = target => target === link;
    app.node('[data-category-bar]').contains = target => target === link;
    app.handlers.get('[data-catalog-path]:pointerenter')({ pointerType: 'mouse' });
    app.handlers.get('catalog:click')({ button: 0, target: link, preventDefault() {} });
    app.handlers.get('document:click')({ target: link });
    assert.equal(path.open, false);
    assert.equal(active.hidden, true);
    assert.equal(panel.hidden, false);
    assert.equal(panel.inputs[0].checked, false);
    assert.equal(app.form.elements.category.value, 'hinges');
    assert.equal(app.form.elements.sort.value, 'price_desc');
    await app.respond(0, { active: '', filters: 'RESET FILTERS' });
    assert.equal(path.open, false);
    assert.equal(active.innerHTML, '');
    assert.equal(panel.hidden, false);
});

test('facet selection summaries update immediately for choices and numeric ranges', () => {
    const app = setup();
    const selection = { textContent: '' };
    const facet = { querySelector: () => selection, querySelectorAll: selector => selector.includes('number') ? [] : [{}, {}] };
    app.handlers.get('form:change')({ target: { name: 'filters[1][]', matches: () => true, closest: () => facet } });
    assert.equal(selection.textContent, 'Vybrané: 2');
    facet.querySelectorAll = () => [];
    app.handlers.get('form:change')({ target: { name: 'filters[1][]', matches: () => true, closest: () => facet } });
    assert.equal(selection.textContent, 'Vybrané: 0');
    facet.querySelectorAll = selector => selector.includes('number') ? [{ name: 'filters[2][min]', value: '0' }, { name: 'filters[2][max]', value: '120' }] : [];
    app.handlers.get('form:input')({ target: { matches: selector => selector.includes('input[type="number"]'), closest: () => facet } });
    assert.equal(selection.textContent, 'Od 0 · Do 120');
});

test('outside click closes category filters while inside click keeps them open', () => {
    const app = setup();
    const inside = {};
    const summary = {};
    app.node('[data-catalog-path]').contains = target => target === summary;
    app.node('[data-category-bar]').contains = target => target === inside;
    app.node('[data-catalog-facets]').hidden = false;
    app.handlers.get('document:click')({ target: inside });
    assert.equal(app.node('[data-catalog-facets]').hidden, false);
    app.handlers.get('document:click')({ target: summary });
    assert.equal(app.node('[data-catalog-facets]').hidden, false);
    app.handlers.get('document:click')({ target: {} });
    assert.equal(app.node('[data-catalog-facets]').hidden, true);
});

test('category button opens its filters, preserves search and Escape restores focus', async () => {
    const app = setup();
    let focused = false;
    const input = {};
    const button = {
        dataset: { catalogCategory: 'hinges' }, attributes: {},
        setAttribute(key, value) { this.attributes[key] = value; },
        removeAttribute(key) { delete this.attributes[key]; },
        getBoundingClientRect() { return { left: 900, bottom: 44 }; },
        focus() { focused = true; },
    };
    app.categoryButtons.push(button);
    app.form.elements.q.value = 'Blum';
    app.form.elements.sort.value = 'price_asc';
    app.handlers.get('catalog:click')({ button: 0, preventDefault() {}, target: { closest: () => button } });
    assert.equal(app.pending[0].url.searchParams.get('category'), 'hinges');
    assert.equal(app.pending[0].url.searchParams.get('q'), 'Blum');
    assert.equal(app.pending[0].url.searchParams.get('sort'), 'price_asc');
    assert.equal(button.attributes['aria-expanded'], 'true');
    await app.respond(0, { filters: 'HINGE FILTERS' });
    assert.equal(app.node('[data-catalog-facets]').hidden, false);
    app.document.activeElement = input;
    app.node('[data-category-bar]').contains = target => target === input;
    app.handlers.get('document:keydown')({ key: 'Escape' });
    assert.equal(app.node('[data-catalog-facets]').hidden, true);
    assert.equal(button.attributes['aria-expanded'], 'false');
    assert.equal(focused, true);
});

test('unchecking applies immediately, updates summary and URL, and keeps filters open', async () => {
    const app = setup();
    app.form.elements.category.value = 'hinges';
    app.form.filterEntries = [['filters[1][]', 'Blum']];
    app.node('[data-catalog-facets]').hidden = false;
    const event = { target: { name: 'filters[1][]', matches: () => true, closest: () => null } };
    app.handlers.get('form:change')(event);
    await app.respond(0, { results: 'FILTERED', active: 'Brand: Blum', total: 1 });
    app.form.filterEntries = [];
    app.handlers.get('form:change')(event);
    assert.equal(app.pending.length, 2);
    assert.equal(app.pending[1].url.searchParams.has('filters[1][]'), false);
    await app.respond(1, { results: 'ALL HINGES', active: '', total: 3 });
    assert.equal(app.node('[data-catalog-results]').innerHTML, 'ALL HINGES');
    assert.equal(app.node('[data-catalog-active]').innerHTML, '');
    assert.equal(app.node('[data-catalog-facets]').hidden, false);
    assert.equal(new URL(app.location.href).searchParams.has('filters[1][]'), false);
});