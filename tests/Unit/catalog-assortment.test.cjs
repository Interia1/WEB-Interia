const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

const source = readFileSync(new URL('../../public/js/catalog-assortment.js', `file://${__filename}`), 'utf8');

function setup() {
    const handlers = new Map();
    const timers = new Map();
    let timerId = 0;
    const element = name => ({
        hidden: false, style: {}, attributes: {}, dataset: {}, value: '',
        addEventListener(event, handler) { handlers.set(`${name}:${event}`, handler); },
        setAttribute(key, value) { this.attributes[key] = value; },
        removeAttribute(key) { delete this.attributes[key]; },
        contains(target) { return target === this; },
        focus() { document.activeElement = this; },
    });
    const trigger = element('trigger');
    trigger.getBoundingClientRect = () => ({ left: 80, bottom: 190 });
    const panel = element('panel');
    panel.hidden = true;
    const search = element('search');
    const closeButton = element('close');
    const empty = element('empty');
    const category = element('category');
    category.dataset = { assortmentCategory: 'hinges', assortmentTerms: 'Nábytkové závesy Výrobca Blum' };
    const link = element('link');
    const inputs = [
        { name: 'category', value: 'hinges', type: 'hidden' },
        { name: 'q', value: '', type: 'hidden' },
        { name: 'sort', value: '', type: 'hidden' },
        { name: 'filters[1][]', value: 'Blum', type: 'checkbox', checked: false },
    ];
    category.querySelector = () => link;
    const selection = { textContent: '' };
    const facet = { querySelectorAll: () => [inputs[3]], querySelector: () => selection };
    category.querySelectorAll = selector => selector === '[data-facet]' ? [facet] : inputs;
    panel.querySelectorAll = () => [category];
    panel.querySelector = selector => ({ '[data-assortment-search]': search, '[data-assortment-close]': closeButton, '[data-assortment-empty]': empty })[selector];
    panel.contains = target => [panel, search, closeButton, category, link].includes(target);
    const form = { action: 'http://localhost/eshop/katalog', entries: [['category', 'hinges'], ['q', 'Panel'], ['sort', 'price_desc'], ['filters[1][]', 'Blum']] };
    const document = {
        activeElement: null,
        documentElement: { clientWidth: 1280 },
        querySelector: selector => ({ '[data-assortment]': panel, '[data-assortment-trigger]': trigger, '[data-catalog-form]': form })[selector],
        addEventListener(event, handler) { handlers.set(`document:${event}`, handler); },
    };
    const window = { innerHeight: 900, addEventListener(event, handler) { handlers.set(`window:${event}`, handler); } };
    vm.runInNewContext(source, {
        document, window, location: { href: form.action }, URL, URLSearchParams,
        FormData: class { constructor(input) { this.entries = input.entries; } [Symbol.iterator]() { return this.entries[Symbol.iterator](); } },
        setTimeout(callback) { const id = ++timerId; timers.set(id, callback); return id; },
        clearTimeout(id) { timers.delete(id); },
    });
    const tick = () => { const callbacks = [...timers.values()]; timers.clear(); callbacks.forEach(callback => callback()); };
    return { handlers, timers, tick, trigger, panel, search, category, inputs, link, empty, form, document, window, selection };
}

test('assortment opens on delayed mouse hover, crosses into panel and closes on leave', () => {
    const app = setup();
    app.handlers.get('trigger:pointerenter')({ pointerType: 'touch' });
    assert.equal(app.timers.size, 0);
    app.handlers.get('trigger:pointerenter')({ pointerType: 'mouse' });
    assert.equal(app.panel.hidden, true);
    app.tick();
    assert.equal(app.panel.hidden, false);
    assert.equal(app.trigger.attributes['aria-expanded'], 'true');
    assert.equal(app.document.activeElement, null);
    app.handlers.get('trigger:pointerleave')({ pointerType: 'mouse' });
    app.handlers.get('panel:pointerenter')();
    app.tick();
    assert.equal(app.panel.hidden, false);
    app.handlers.get('panel:pointerleave')({ pointerType: 'mouse' });
    app.tick();
    assert.equal(app.panel.hidden, true);
});

test('assortment click pins, Escape returns focus and outside click dismisses', () => {
    const app = setup();
    app.handlers.get('trigger:click')();
    assert.equal(app.panel.hidden, false);
    assert.equal(app.document.activeElement, app.search);
    app.handlers.get('panel:pointerleave')({ pointerType: 'mouse' });
    app.tick();
    assert.equal(app.panel.hidden, false);
    app.handlers.get('document:keydown')({ key: 'Escape', preventDefault() {} });
    assert.equal(app.panel.hidden, true);
    assert.equal(app.document.activeElement, app.trigger);
    app.handlers.get('trigger:keydown')({ key: 'ArrowDown', preventDefault() {} });
    assert.equal(app.panel.hidden, false);
    app.handlers.get('document:click')({ target: {} });
    assert.equal(app.panel.hidden, true);
    app.handlers.get('trigger:click')();
    app.handlers.get('trigger:click')();
    assert.equal(app.panel.hidden, true);
});

test('assortment searches without accents and refreshes filters from the live catalog', () => {
    const app = setup();
    app.handlers.get('trigger:click')();
    assert.equal(app.inputs[3].checked, true);
    assert.equal(app.selection.textContent, 'Vybrané: 1');
    assert.equal(app.inputs[1].value, 'Panel');
    assert.equal(app.inputs[2].value, 'price_desc');
    assert.equal(app.link.attributes['aria-current'], 'true');
    assert.equal(new URL(app.link.href).searchParams.has('filters[1][]'), false);
    app.search.value = 'zavesy blum';
    app.handlers.get('search:input')();
    assert.equal(app.category.hidden, false);
    assert.equal(app.empty.hidden, true);
    app.search.value = 'nonexistent';
    app.handlers.get('search:input')();
    assert.equal(app.category.hidden, true);
    assert.equal(app.empty.hidden, false);
    app.handlers.get('close:click')();
    app.form.entries = [['category', 'boards'], ['sort', 'name'], ['q', '']];
    app.handlers.get('trigger:click')();
    assert.equal(app.inputs[3].checked, false);
    assert.equal(app.selection.textContent, 'Vybrané: 0');
    assert.equal(app.inputs[2].value, 'name');
    assert.equal(app.link.attributes['aria-current'], undefined);
    app.document.documentElement.clientWidth = 375;
    app.handlers.get('window:resize')();
    assert.equal(app.panel.style.width, '351px');
    assert.equal(app.panel.style.left, '12px');
});