const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

const source = readFileSync(new URL('../../public/js/history-navigation.js', `file://${__filename}`), 'utf8');

function setup(withNavigation = true) {
    const handlers = new Map();
    const calls = [];
    const button = name => ({
        disabled: false,
        addEventListener(event, handler) { handlers.set(`${name}:${event}`, handler); },
    });
    const back = button('back');
    const forward = button('forward');
    const navigation = {
        canGoBack: false,
        canGoForward: false,
        addEventListener(event, handler) { handlers.set(`navigation:${event}`, handler); },
    };
    const history = { length: 1, back() { calls.push('back'); }, forward() { calls.push('forward'); } };
    vm.runInNewContext(source, {
        document: {
            querySelector(selector) { return selector === '[data-history-back]' ? back : forward; },
            addEventListener(event, handler) { handlers.set(`document:${event}`, handler); },
        },
        window: {
            navigation: withNavigation ? navigation : undefined,
            history,
            addEventListener(event, handler) { handlers.set(`window:${event}`, handler); },
        },
    });
    handlers.get('document:DOMContentLoaded')();
    return { handlers, back, forward, navigation, history, calls };
}

test('same-document history updates enable back and forward without a page navigation', () => {
    const app = setup();
    assert.equal(app.back.disabled, true);
    assert.equal(app.forward.disabled, true);
    app.navigation.canGoBack = true;
    app.handlers.get('navigation:currententrychange')();
    assert.equal(app.back.disabled, false);
    app.handlers.get('back:click')();
    app.navigation.canGoBack = false;
    app.navigation.canGoForward = true;
    app.handlers.get('window:popstate')();
    assert.equal(app.back.disabled, true);
    assert.equal(app.forward.disabled, false);
    app.handlers.get('forward:click')();
    assert.deepEqual(app.calls, ['back', 'forward']);
    app.navigation.canGoBack = true;
    app.navigation.canGoForward = false;
    app.handlers.get('navigation:currententrychange')();
    assert.equal(app.back.disabled, false);
    assert.equal(app.forward.disabled, true);
});

test('catalog history changes refresh fallback buttons without the Navigation API', () => {
    const app = setup(false);
    assert.equal(app.back.disabled, true);
    app.history.length = 2;
    app.handlers.get('window:catalog:historychange')();
    assert.equal(app.back.disabled, false);
    app.handlers.get('back:click')();
    app.handlers.get('forward:click')();
    assert.deepEqual(app.calls, ['back', 'forward']);
});