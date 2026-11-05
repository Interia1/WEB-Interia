const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

const source = readFileSync(new URL('../../public/js/cart-picker.js', `file://${__filename}`), 'utf8');
const styles = readFileSync(new URL('../../public/css/app.css', `file://${__filename}`), 'utf8');

test('cart options are slightly right of the arrow center', () => {
    assert.match(styles, /\.shop-cart-choices\s*\{\s*position:\s*relative;/);
    assert.match(styles, /\.shop-cart-options\s*\{\s*position:\s*absolute;\s*top:\s*100%;\s*left:\s*calc\(50% \+ 1rem\);\s*transform:\s*translateX\(-50%\);/);
});

test('the green border and arrow point to the cart dropdown control', () => {
    assert.match(styles, /\.shop-cart-options\s*\{[^}]*border:\s*1px solid #198754;/);
    assert.match(styles, /\.shop-cart-choices\[open\]::before,\s*\.shop-cart-choices\[open\]::after\s*\{[^}]*left:\s*50%;[^}]*pointer-events:\s*none;/);
    assert.match(styles, /\.shop-cart-choices\[open\]::before\s*\{[^}]*border-bottom:\s*7px solid #198754;/);
    assert.match(styles, /\.shop-cart-choices\[open\]::after\s*\{[^}]*border-bottom:\s*6px solid #fff;/);
});

test('hovered or focused cart options have bold, underlined text', () => {
    assert.match(styles, /\.shop-cart-option:hover,\s*\.shop-cart-option:focus-within\s*\{\s*background:\s*#bdd9fa;/);
    assert.match(styles, /\.shop-cart-option:hover \.shop-cart-option-add,\s*\.shop-cart-option:focus-within \.shop-cart-option-add\s*\{\s*font-weight:\s*700;\s*text-decoration:\s*underline;\s*text-decoration-thickness:\s*2px;/);
});

test('header cart choices use the product cart colors and distinguish the default', () => {
    assert.match(styles, /\.cart-menu-cart-name,\s*\.cart-menu-select\s*\{[^}]*width:\s*100%;[^}]*padding:\s*0\.35rem 0\.45rem;/);
    assert.match(styles, /\.cart-menu-cart-name,\s*\.cart-menu-select\s*\{[^}]*font-size:\s*1\.05rem;[^}]*font-weight:\s*500;/);
    assert.match(styles, /\.cart-menu-cart-name\s*\{[^}]*background:\s*#0d6efd;[^}]*color:\s*#fff;/);
    assert.match(styles, /\.cart-menu-select\s*\{[^}]*background:\s*#eaf3ff;[^}]*color:\s*#28415f;/);
    const hover = styles.match(/\.cart-menu-select:hover,\s*\.cart-menu-select:focus-visible\s*\{([^}]*)\}/);
    assert.ok(hover);
    assert.match(hover[1], /background:\s*#bdd9fa;[^}]*text-decoration:\s*underline;/);
    assert.doesNotMatch(hover[1], /font-weight\s*:/);
    assert.match(styles, /\.cart-menu-select:focus-visible\s*\{[^}]*outline:\s*2px solid #245b91;/);
});

test('header cart has a thin green border and an upward pointer', () => {
    assert.match(styles, /\.site-nav \.cart-menu-dropdown\s*\{[^}]*border:\s*1px solid #198754;/);
    assert.match(styles, /\.site-nav \.cart-menu-dropdown\s*\{[^}]*left:\s*var\(--cart-menu-left,[^;]*;[^}]*transform:\s*none;/);
    assert.match(styles, /\.site-nav \.cart-menu-dropdown::before,\s*\.site-nav \.cart-menu-dropdown::after\s*\{[^}]*left:\s*var\(--cart-arrow-position, 50%\);[^}]*transform:\s*translateX\(-50%\);/);
    assert.match(styles, /\.site-nav \.cart-menu-dropdown::before\s*\{[^}]*top:\s*-7px;[^}]*border-bottom:\s*7px solid #198754;/);
    assert.match(styles, /\.site-nav \.cart-menu-dropdown::after\s*\{[^}]*top:\s*-5px;[^}]*border-bottom:\s*6px solid #fff;/);
    assert.match(styles, /@media \(max-width:\s*1199\.98px\)\s*\{\s*\.site-nav \.cart-menu-dropdown\s*\{\s*position:\s*relative;/);
});

test('header cart contents have a prominent divider and an unshaded small heading', () => {
    assert.match(styles, /\.cart-menu-carts \+ \.cart-menu-items-title\s*\{[^}]*padding-top:\s*0\.75rem;[^}]*border-top:\s*3px solid #0d6efd;/);
    const heading = styles.match(/\.cart-menu-items-title\s*\{([^}]*)\}/);
    assert.ok(heading);
    assert.match(heading[1], /font-size:\s*0\.875rem;/);
    assert.doesNotMatch(heading[1], /background(?:-color)?\s*:/);
});

test('header cart product links are not underlined and retain a focus indicator', () => {
    assert.match(styles, /\.cart-menu-item a\s*\{[^}]*color:\s*#1658c1;[^}]*font-size:\s*1\.05rem;[^}]*font-weight:\s*500;/);
    assert.match(styles, /\.cart-menu-item a,\s*\.cart-menu-item a:hover,\s*\.cart-menu-item a:focus-visible\s*\{[^}]*text-decoration:\s*none;/);
    assert.match(styles, /\.cart-menu-item a:focus-visible\s*\{[^}]*outline:\s*2px solid #245b91;/);
    assert.match(styles, /\.cart-menu-item-meta\s*\{[^}]*color:\s*#334155;[^}]*font-size:\s*0\.9375rem;[^}]*font-weight:\s*500;/);
});

test('cart options reserve space for bold text before hover', () => {
    assert.match(styles, /\.shop-cart-option\s*\{\s*display:\s*grid;\s*grid-template-columns:\s*minmax\(0,\s*1fr\) auto;/);
    assert.match(styles, /\.shop-cart-option-name > span\s*\{\s*grid-area:\s*1 \/ 1;/);
    assert.match(styles, /\.shop-cart-option-width\s*\{\s*visibility:\s*hidden;\s*font-weight:\s*700;/);
});

test('quantity badges expand for large counts and controls can wrap', () => {
    assert.match(styles, /\.shop-cart-quantity\s*\{[^}]*min-width:\s*1\.25rem;[^}]*min-height:\s*1\.25rem;[^}]*font-variant-numeric:\s*tabular-nums;/);
    assert.match(styles, /\.shop-cart-quantity\s*\{[^}]*font-size:\s*0\.75rem;/);
    assert.doesNotMatch(styles, /\.shop-cart-primary-actions \.shop-cart-quantity\s*\{/);
    assert.match(styles, /\.shop-cart-primary-group\s*\{\s*flex:\s*0 1 auto;\s*width:\s*max-content;\s*min-width:\s*0;\s*max-width:\s*100%;\s*font-size:\s*0\.75rem;/);
    assert.match(styles, /\.shop-cart-primary\s*\{[^}]*flex:\s*0 1 calc\(15ch - 1rem\);[^}]*width:\s*calc\(15ch - 1rem\);/);
    assert.doesNotMatch(styles, /\.shop-cart-primary-actions \.shop-cart-quantity-controls\s*\{/);
    assert.match(styles, /\.shop-cart-primary-actions\s*\{[^}]*display:\s*flex;[^}]*width:\s*100%;[^}]*max-width:\s*100%;/);
    assert.match(styles, /\.shop-cart-primary-name\s*\{[^}]*overflow:\s*hidden;[^}]*text-overflow:\s*ellipsis;[^}]*white-space:\s*nowrap;/);
    assert.match(styles, /\.shop-cart-quantity-controls\s*\{[^}]*border-left:\s*1px solid #d1e2f5;[^}]*background:\s*#fff;[^}]*color:\s*#28415f;/);
    assert.match(styles, /\.shop-cart-option\s*\{[^}]*background:\s*#eaf3ff;/);
    const optionControls = styles.match(/\.shop-cart-option \.shop-cart-quantity-controls\s*\{([^}]*)\}/);
    assert.ok(optionControls);
    assert.match(optionControls[1], /background:\s*#fff;/);
    assert.doesNotMatch(optionControls[1], /\b(?:width|padding|flex(?:-basis)?)\s*:/);
    assert.match(styles, /\.shop-product-cart-form\s*\{\s*flex-wrap:\s*wrap;/);
});

test('enabled quantity signs are highlighted on hover and keyboard focus', () => {
    assert.match(styles, /\.shop-cart-adjust:not\(:disabled\):hover,\s*\.shop-cart-adjust:focus-visible\s*\{[^}]*background:\s*#dceaff;[^}]*color:\s*#0646a4;/);
    assert.match(styles, /\.shop-cart-adjust:focus-visible\s*\{\s*outline:\s*2px solid #245b91;/);
});

function setup() {
    const handlers = {};
    const document = {
        activeElement: null,
        addEventListener(name, callback) { handlers[name] = callback; },
        querySelectorAll() { return details.open ? [details] : []; },
    };
    vm.runInNewContext(source, { document });
    const details = {
        open: false,
        dataset: {},
        contains(node) { return node === summary || node === option; },
        querySelector() { return summary; },
    };
    const picker = {
        contains(node) { return node === picker || node === details || node === summary || node === option; },
        querySelector() { return details; },
        closest(selector) { return selector === '[data-cart-picker]' ? picker : null; },
    };
    const summary = {
        parentElement: details,
        contains(node) { return node === summary; },
        focus() { document.activeElement = summary; },
        closest(selector) {
            if (selector === '[data-cart-picker]') return picker;
            if (selector === '[data-cart-choices] > summary') return summary;
            if (selector === '[data-cart-choices]') return details;
            return null;
        },
    };
    const option = {
        closest(selector) {
            if (selector === '[data-cart-picker]') return picker;
            if (selector === '[data-cart-choices]') return details;
            return null;
        },
    };
    return { handlers, document, picker, details, summary, option };
}

test('only hovering the arrow opens the picker and leaving the menu closes it', () => {
    const { handlers, picker, details, summary, option } = setup();
    handlers.pointerover({ pointerType: 'mouse', target: picker, relatedTarget: null });
    assert.equal(details.open, false);
    handlers.pointerover({ pointerType: 'mouse', target: summary, relatedTarget: picker });
    assert.equal(details.open, true);
    handlers.pointerout({ pointerType: 'mouse', target: summary, relatedTarget: option });
    assert.equal(details.open, true);
    handlers.pointerout({ pointerType: 'mouse', target: option, relatedTarget: picker });
    assert.equal(details.open, false);
    handlers.pointerover({ pointerType: 'touch', target: summary, relatedTarget: null });
    assert.equal(details.open, false);
});

test('click keeps a hover-opened picker available and Escape restores focus', () => {
    const { handlers, document, details, summary, option } = setup();
    handlers.pointerover({ pointerType: 'mouse', target: summary, relatedTarget: null });
    let prevented = false;
    handlers.click({ target: summary, preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    handlers.pointerout({ pointerType: 'mouse', target: summary, relatedTarget: null });
    assert.equal(details.open, true);
    handlers.keydown({ key: 'Escape', target: option });
    assert.equal(details.open, false);
    assert.equal(document.activeElement, summary);
});

test('hover exit does not hide a menu containing keyboard focus', () => {
    const { handlers, document, details, summary, option } = setup();
    handlers.pointerover({ pointerType: 'mouse', target: summary, relatedTarget: null });
    document.activeElement = option;
    handlers.pointerout({ pointerType: 'mouse', target: summary, relatedTarget: null });
    assert.equal(details.open, true);
});

test('after changing a quantity with the mouse the menu closes on mouse exit', () => {
    const { handlers, document, details, summary, option } = setup();
    handlers.pointerover({ pointerType: 'mouse', target: summary, relatedTarget: null });
    document.activeElement = option;
    handlers.pointerdown({ pointerType: 'mouse', target: option });
    assert.equal(details.open, true);
    handlers.pointerout({ pointerType: 'mouse', target: option, relatedTarget: null });
    assert.equal(details.open, false);
});

test('clicking outside closes an open picker', () => {
    const { handlers, details, summary } = setup();
    handlers.pointerover({ pointerType: 'mouse', target: summary, relatedTarget: null });
    handlers.click({ target: { closest() { return null; } } });
    assert.equal(details.open, false);
});

function setupCartRequests() {
    const handlers = {};
    const pending = [];
    const timers = [];
    const makeHeader = updated => {
        const toggle = {
            innerHTML: String(updated),
            getAttribute() { return String(updated); },
            setAttribute(name, value) { this[name] = value; },
            focus() {},
        };
        const dropdown = {
            innerHTML: String(updated),
            contains() { return false; },
            querySelector() { return null; },
        };
        return {
            toggle,
            dropdown,
            querySelector(selector) {
                if (selector === '.cart-menu-toggle') return toggle;
                if (selector === '.cart-menu-dropdown') return dropdown;
                return null;
            },
        };
    };
    const header = makeHeader(0);
    const notice = {
        hidden: true,
        classList: { toggle(name, value) { notice[name] = value; } },
        setAttribute(name, value) { this[name] = value; },
    };
    const makeButton = (value, disabled = false) => ({
        value,
        disabled,
        attributes: { 'aria-label': `quantity ${value}` },
        setAttribute(name, value) { this.attributes[name] = value; },
        getAttribute(name) { return this.attributes[name]; },
    });
    const makeControl = (key, quantity) => {
        const badge = {
            textContent: String(quantity),
            attributes: { 'aria-label': `${quantity} ks` },
            setAttribute(name, value) { this.attributes[name] = value; },
            getAttribute(name) { return this.attributes[name]; },
        };
        const minus = makeButton('decrease', quantity === 0);
        const plus = makeButton('increase', quantity === 100000);
        return {
            dataset: { cartKey: key },
            badge,
            minus,
            plus,
            querySelector(selector) {
                if (selector === '.shop-cart-quantity') return badge;
                if (selector.includes('decrease')) return minus;
                if (selector.includes('increase')) return plus;
                return null;
            },
            querySelectorAll(selector) { return selector === '.shop-cart-adjust' ? [minus, plus] : []; },
        };
    };
    const makePicker = quantities => {
        const controls = Object.entries(quantities).map(([key, value]) => makeControl(key, value));
        const primary = makeButton('primary');
        const options = Object.keys(quantities).filter(key => key !== 'default').map(key => makeButton(key));
        return {
            controls,
            primary,
            options,
            querySelector(selector) {
                if (selector === '[data-cart-notice]') return notice;
                if (selector === '.shop-cart-primary') return primary;
                return null;
            },
            querySelectorAll(selector) {
                if (selector === '[data-cart-key]') return controls;
                if (selector === '.shop-cart-option-add') return options;
                return [];
            },
        };
    };
    const picker = makePicker({ default: 0, 7: 0 });
    const headerError = { hidden: true };
    const row = { id: 'catalog-product-1', querySelector() { return picker; } };
    const form = {
        action: '/add',
        matches(selector) { return selector === '.shop-product-cart-form'; },
        closest() { return row; },
        querySelector() { return picker.primary; },
    };
    const submitter = {
        name: 'change',
        value: 'increase',
        hasAttribute(name) { return name === 'formaction'; },
        getAttribute() { return '/adjust?cart_id=7'; },
    };
    const document = {
        addEventListener(name, callback) { handlers[name] = callback; },
        getElementById(id) { return id === row.id ? row : null; },
        querySelector(selector) {
            if (selector === '.cart-menu') return header;
            if (selector === '[data-header-cart-error]') return headerError;
            return null;
        },
        querySelectorAll() { return []; },
    };
    class FakeFormData {
        constructor() { this.values = []; }
        append(name, value) { this.values.push([name, value]); }
    }
    class FakeParser {
        parseFromString(value) {
            return { querySelector(selector) {
                if (selector === '[data-cart-picker]') return value;
                if (selector === '.cart-menu') return value;
                return null;
            } };
        }
    }
    vm.runInNewContext(source, {
        document,
        window: { requestAnimationFrame() {} },
        FormData: FakeFormData,
        DOMParser: FakeParser,
        fetch(url, options) { return new Promise(resolve => pending.push({ url, options, resolve })); },
        setTimeout(callback) { timers.push(callback); return timers.length; },
        clearTimeout() {},
    });
    const submit = () => {
        let prevented = false;
        handlers.submit({ target: form, submitter, preventDefault() { prevented = true; } });
        assert.equal(prevented, true);
    };
    return { handlers, pending, picker, notice, header, headerError, makeHeader, makePicker, submit };
}

test('cart updates are queued and update counts without replacing the open picker', async () => {
    const app = setupCartRequests();
    app.submit();
    app.submit();
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(app.pending.length, 1);
    assert.equal(app.pending[0].url, '/adjust?cart_id=7');
    assert.deepEqual(app.pending[0].options.body.values, [['change', 'increase']]);
    assert.equal(app.pending[0].options.headers.Accept, 'application/json');
    app.pending[0].resolve({ ok: true, json: async () => ({
        product_id: 1, results: app.makePicker({ default: 0, 7: 3 }), header: app.makeHeader(1), message: 'Upravené',
    }) });
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(app.pending.length, 2);
    assert.equal(app.picker.controls.find(control => control.dataset.cartKey === '7').badge.textContent, '3');
    assert.equal(app.picker.controls.find(control => control.dataset.cartKey === '7').minus.disabled, false);
    assert.equal(app.notice.textContent, 'Upravené');
    assert.equal(app.notice.hidden, false);
    assert.equal(app.header.toggle.innerHTML, '1');
    app.pending[1].resolve({ ok: true, json: async () => ({
        product_id: 1, results: app.makePicker({ default: 0, 7: 4 }), header: app.makeHeader(2), message: 'Upravené',
    }) });
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(app.picker.controls.find(control => control.dataset.cartKey === '7').badge.textContent, '4');
    assert.equal(app.header.toggle.innerHTML, '2');
});

test('cart validation errors are displayed next to the affected product', async () => {
    const app = setupCartRequests();
    app.submit();
    await new Promise(resolve => setImmediate(resolve));
    app.pending[0].resolve({ ok: false, json: async () => ({ errors: { quantity: ['Limit je prekročený.'] } }) });
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(app.notice.textContent, 'Limit je prekročený.');
    assert.equal(app.notice.hidden, false);
    assert.equal(app.notice['is-error'], true);
    assert.equal(app.notice.role, 'alert');
    assert.equal(app.picker.controls.find(control => control.dataset.cartKey === '7').badge.textContent, '0');
});

test('removing a header item refreshes its product controls without replacing the header', async () => {
    const app = setupCartRequests();
    const form = {
        action: '/remove',
        dataset: { headerCartAction: 'remove' },
        matches(selector) { return selector === '[data-header-cart-action]'; },
    };
    app.handlers.submit({ target: form, preventDefault() {} });
    await new Promise(resolve => setImmediate(resolve));
    app.pending[0].resolve({ ok: true, json: async () => ({
        product_id: 1, results: app.makePicker({ default: 0, 7: 3 }), header: app.makeHeader(1),
    }) });
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(app.header.toggle.innerHTML, '1');
    assert.equal(app.picker.controls.find(control => control.dataset.cartKey === '7').badge.textContent, '3');
});

test('header update errors are shown in the menu', async () => {
    const app = setupCartRequests();
    const form = {
        action: '/remove',
        dataset: { headerCartAction: 'remove' },
        matches(selector) { return selector === '[data-header-cart-action]'; },
    };
    app.handlers.submit({ target: form, preventDefault() {} });
    await new Promise(resolve => setImmediate(resolve));
    app.pending[0].resolve({ ok: false, json: async () => ({ message: 'Košík sa nepodarilo upraviť.' }) });
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(app.headerError.hidden, false);
    assert.equal(app.headerError.textContent, 'Košík sa nepodarilo upraviť.');
});

test('header changes keep the same open menu and close it on mouse exit', async () => {
    const handlers = {};
    const pending = [];
    let closed = 0;
    const toggle = {
        innerHTML: 'old',
        setAttribute(name, value) { this[name] = value; },
        getAttribute() { return 'new label'; },
        focus() {},
    };
    const dropdown = {
        innerHTML: 'old contents',
        contains() { return false; },
        querySelector() { return null; },
    };
    const header = {
        dataset: {},
        contains(node) { return node === inside; },
        querySelector(selector) { return selector === '.cart-menu-toggle' ? toggle : dropdown; },
    };
    const inside = {
        closest(selector) { return selector === '.cart-menu' ? header : null; },
    };
    const picker = { replaceWith(node) { this.replacement = node; } };
    const row = { id: 'catalog-product-1', querySelector() { return picker; } };
    const freshPicker = {};
    const freshHeader = {
        querySelector(selector) {
            if (selector === '.cart-menu-toggle') return { innerHTML: 'new', getAttribute() { return 'new label'; } };
            return { innerHTML: 'new contents' };
        },
    };
    const document = {
        activeElement: null,
        addEventListener(name, callback) { handlers[name] = callback; },
        querySelector(selector) { return selector === '.cart-menu' ? header : null; },
        querySelectorAll(selector) { return selector.startsWith('.shop-product-row') ? [row] : []; },
    };
    class FakeFormData {
        constructor() { this.values = []; }
        append(name, value) { this.values.push([name, value]); }
    }
    class FakeParser {
        parseFromString(value) {
            if (value === 'header') return { querySelector() { return freshHeader; } };
            return { getElementById() { return { querySelector() { return freshPicker; } }; } };
        }
    }
    vm.runInNewContext(source, {
        document, FormData: FakeFormData, DOMParser: FakeParser,
        window: { requestAnimationFrame() {}, bootstrap: { Dropdown: { getInstance() { return { hide() { closed++; } }; } } } },
        fetch(url, options) { return new Promise(resolve => pending.push({ url, options, resolve })); },
    });
    const form = {
        action: '/default',
        dataset: { headerCartAction: 'default' },
        matches(selector) { return selector === '[data-header-cart-action]'; },
    };
    handlers.pointerdown({ pointerType: 'mouse', target: inside });
    let prevented = false;
    handlers.submit({ target: form, preventDefault() { prevented = true; } });
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(prevented, true);
    assert.deepEqual(pending[0].options.body.values, [['product_ids[]', '1']]);
    pending[0].resolve({ ok: true, json: async () => ({ header: 'header', results: 'results' }) });
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(toggle.innerHTML, 'new');
    assert.equal(dropdown.innerHTML, 'new contents');
    assert.equal(picker.replacement, freshPicker);
    assert.equal(closed, 0);
    handlers.pointerout({ pointerType: 'mouse', target: inside, relatedTarget: null });
    assert.equal(closed, 1);
});

test('header menu centers under the cart and its pointer tracks the button near viewport edges', () => {
    for (const { parent, button, viewport, expectedLeft, expectedArrow } of [
        { parent: { left: 350, width: 100 }, button: { left: 350, width: 100 }, viewport: 1200, expectedLeft: 250, expectedArrow: 150 },
        { parent: { left: 350.3, width: 100 }, button: { left: 350.3, width: 100 }, viewport: 1200, expectedLeft: 250, expectedArrow: 150.3 },
        { parent: { left: 28, width: 320 }, button: { left: 40, width: 150 }, viewport: 360, expectedLeft: 8, expectedArrow: 107 },
        { parent: { left: 1100, width: 100 }, button: { left: 1100, width: 100 }, viewport: 1200, expectedLeft: 892, expectedArrow: 258 },
    ]) {
        const handlers = {};
        const properties = {};
        const dropdown = {
            getBoundingClientRect() { return { width: 300 }; },
            style: { setProperty(name, value) { properties[name] = parseFloat(value); } },
        };
        const toggle = { getBoundingClientRect() { return button; }, matches() { return true; } };
        const header = {
            getBoundingClientRect() { return parent; },
            querySelector(selector) { return selector === '.cart-menu-toggle' ? toggle : dropdown; },
        };
        const document = {
            addEventListener(name, callback) { handlers[name] = callback; },
            querySelector(selector) { return selector === '.cart-menu' ? header : null; },
        };
        vm.runInNewContext(source, { document, window: { innerWidth: viewport } });
        handlers['shown.bs.dropdown']({ target: toggle });
        const actualLeft = parent.left + properties['--cart-menu-left'];
        assert.equal(actualLeft, expectedLeft);
        assert.ok(Math.abs(properties['--cart-arrow-position'] - expectedArrow) < 0.001);
        assert.ok(Math.abs(actualLeft + properties['--cart-arrow-position'] - button.left - button.width / 2) < 0.001);
    }
});
