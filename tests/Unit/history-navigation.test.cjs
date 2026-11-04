const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

const source = readFileSync(new URL('../../public/js/history-navigation.js', `file://${__filename}`), 'utf8');
const styles = readFileSync(new URL('../../public/css/app.css', `file://${__filename}`), 'utf8');

function setup(withNavigation = true, previous = {}) {
    const handlers = new Map();
    const calls = [];
    const button = name => ({
        disabled: false,
        attributes: {},
        setAttribute(name, value) { this.attributes[name] = value; },
        addEventListener(event, handler) { handlers.set(`${name}:${event}`, handler); },
    });
    const back = button('back');
    const forward = button('forward');
    const backCount = {};
    const forwardCount = {};
    const entries = previous.navigation?.entries() || [{ key: 'first' }];
    const navigation = {
        canGoBack: previous.navigation?.canGoBack ?? false,
        canGoForward: previous.navigation?.canGoForward ?? false,
        currentEntry: previous.navigation?.currentEntry || entries[0],
        entries() { return entries; },
        addEventListener(event, handler) { handlers.set(`navigation:${event}`, handler); },
    };
    const history = previous.history || {
        length: 1,
        state: null,
        replaceState(state) { this.state = state; },
        back() { calls.push('back'); },
        forward() { calls.push('forward'); },
    };
    const storage = previous.storage || new Map();
    const sessionStorage = {
        getItem(key) { return storage.get(key) ?? null; },
        setItem(key, value) { storage.set(key, value); },
    };
    const elements = {
        '[data-history-back]': back,
        '[data-history-forward]': forward,
        '[data-history-back-count]': backCount,
        '[data-history-forward-count]': forwardCount,
    };
    vm.runInNewContext(source, {
        document: {
            querySelector(selector) { return elements[selector]; },
            addEventListener(event, handler) { handlers.set(`document:${event}`, handler); },
        },
        window: {
            navigation: withNavigation ? navigation : undefined,
            history,
            sessionStorage,
            addEventListener(event, handler) { handlers.set(`window:${event}`, handler); },
        },
        console,
    });
    handlers.get('document:DOMContentLoaded')();
    return { handlers, back, forward, backCount, forwardCount, navigation, entries, history, storage, calls };
}

test('Navigation API counts steps and updates buttons on history changes', () => {
    const app = setup();
    assert.equal(app.backCount.textContent, 0);
    assert.equal(app.forwardCount.textContent, 0);
    assert.equal(app.back.disabled, true);
    assert.equal(app.forward.disabled, true);

    for (let i = 0; i < 5; i++) app.entries.push({ key: `entry-${i}` });
    app.navigation.currentEntry = app.entries[5];
    app.navigation.canGoBack = true;
    app.handlers.get('navigation:currententrychange')();
    assert.equal(app.backCount.textContent, 5);
    assert.equal(app.forwardCount.textContent, 0);
    assert.match(app.back.attributes['aria-label'], /5/);
    assert.equal(app.back.disabled, false);

    app.navigation.currentEntry = app.entries[3];
    app.navigation.canGoForward = true;
    app.handlers.get('window:popstate')();
    assert.equal(app.backCount.textContent, 3);
    assert.equal(app.forwardCount.textContent, 2);
    assert.equal(app.forward.disabled, false);
    app.handlers.get('back:click')();
    app.handlers.get('forward:click')();
    assert.deepEqual(app.calls, ['back', 'forward']);
});

test('Navigation API preserves history on home navigation and reload, and discards a forward branch', () => {
    const app = setup();
    app.entries.push({ key: 'catalog' }, { key: 'product' });
    app.navigation.currentEntry = app.entries[2];
    app.navigation.canGoBack = true;
    app.handlers.get('navigation:navigatesuccess')();
    assert.equal(app.backCount.textContent, 2);

    app.entries.push({ key: 'home-from-logo' });
    app.navigation.currentEntry = app.entries[3];
    const home = setup(true, { navigation: app.navigation });
    assert.equal(home.backCount.textContent, 3);
    assert.equal(home.forwardCount.textContent, 0);

    const reloaded = setup(true, { navigation: app.navigation });
    assert.equal(reloaded.backCount.textContent, 3);
    assert.equal(reloaded.forwardCount.textContent, 0);

    reloaded.navigation.currentEntry = app.entries[1];
    reloaded.navigation.canGoForward = true;
    reloaded.handlers.get('window:popstate')();
    assert.equal(reloaded.backCount.textContent, 1);
    assert.equal(reloaded.forwardCount.textContent, 2);

    app.entries.splice(2, 2, { key: 'new-page' });
    reloaded.navigation.currentEntry = app.entries[2];
    reloaded.navigation.canGoForward = false;
    reloaded.handlers.get('navigation:currententrychange')();
    assert.equal(reloaded.backCount.textContent, 2);
    assert.equal(reloaded.forwardCount.textContent, 0);
});

test('fallback tracks new entries, traversal, reload and a new branch', () => {
    const app = setup(false);
    const states = [app.history.state];
    for (let i = 0; i < 5; i++) {
        app.history.state = null;
        app.history.length++;
        app.handlers.get('window:catalog:historychange')();
        states.push(app.history.state);
    }
    assert.equal(app.backCount.textContent, 5);
    assert.equal(app.forwardCount.textContent, 0);
    assert.equal(app.back.disabled, false);

    app.history.state = states[3];
    app.handlers.get('window:popstate')();
    assert.equal(app.backCount.textContent, 3);
    assert.equal(app.forwardCount.textContent, 2);

    const reloaded = setup(false, { history: app.history, storage: app.storage });
    assert.equal(reloaded.backCount.textContent, 3);
    assert.equal(reloaded.forwardCount.textContent, 2);
    reloaded.history.state = null;
    reloaded.handlers.get('window:catalog:historychange')();
    assert.equal(reloaded.backCount.textContent, 4);
    assert.equal(reloaded.forwardCount.textContent, 0);

    reloaded.history.state = null;
    reloaded.history.length++;
    const nextPage = setup(false, { history: reloaded.history, storage: reloaded.storage });
    assert.equal(nextPage.backCount.textContent, 5);
    assert.equal(nextPage.forwardCount.textContent, 0);
    nextPage.history.state = states[0];
    nextPage.handlers.get('window:pageshow')();
    assert.equal(nextPage.backCount.textContent, 0);
    assert.equal(nextPage.forwardCount.textContent, 5);
});

test('fallback tracks fragment navigation and starts a fresh tab at zero', () => {
    const app = setup(false);
    app.history.state = null;
    app.history.length++;
    app.handlers.get('window:hashchange')();
    assert.equal(app.backCount.textContent, 1);

    const freshTabHistory = { ...app.history, length: 1, state: null };
    const freshTab = setup(false, { history: freshTabHistory, storage: new Map(app.storage) });
    assert.equal(freshTab.backCount.textContent, 0);
    assert.equal(freshTab.forwardCount.textContent, 0);
});

test('history buttons keep the hover colors while pressed without a transition', () => {
    const buttonRule = styles.match(/\.history-nav-button\s*\{([^}]*)\}/);
    assert.ok(buttonRule);
    assert.doesNotMatch(buttonRule[1], /\btransition\s*:/);
    assert.match(styles, /\.history-nav-button:hover,\s*\.history-nav-button:active,\s*\.history-nav-button:focus-visible\s*\{[^}]*background:\s*#dcecff;/);
    assert.match(styles, /\.history-nav-button:active \.history-nav-count[^{]*\{[^}]*opacity:\s*1;/);
});
