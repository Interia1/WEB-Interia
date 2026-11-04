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
    assert.match(styles, /\.shop-cart-option:hover,\s*\.shop-cart-option:focus-visible\s*\{\s*background:\s*#bdd9fa;\s*font-weight:\s*700;\s*text-decoration:\s*underline;\s*text-decoration-thickness:\s*2px;/);
});

test('cart options reserve space for bold text before hover', () => {
    assert.match(styles, /\.shop-cart-option\s*\{\s*display:\s*grid;\s*grid-template-columns:\s*minmax\(0,\s*1fr\);/);
    assert.match(styles, /\.shop-cart-option > span\s*\{\s*grid-area:\s*1 \/ 1;/);
    assert.match(styles, /\.shop-cart-option-width\s*\{\s*visibility:\s*hidden;\s*font-weight:\s*700;/);
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

test('clicking outside closes an open picker', () => {
    const { handlers, details, summary } = setup();
    handlers.pointerover({ pointerType: 'mouse', target: summary, relatedTarget: null });
    handlers.click({ target: { closest() { return null; } } });
    assert.equal(details.open, false);
});
