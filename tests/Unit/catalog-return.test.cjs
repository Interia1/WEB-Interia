const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

const source = readFileSync(new URL('../../public/js/catalog-return.js', `file://${__filename}`), 'utf8');

function setup() {
    const handlers = new Map();
    const storage = new Map();
    const scrolls = [];
    const location = new URL('http://localhost/eshop/katalog?category=hinges&page=2');
    const sessionStorage = {
        setItem(key, value) { storage.set(key, value); },
        getItem(key) { return storage.get(key) ?? null; },
        removeItem(key) { storage.delete(key); },
    };
    vm.runInNewContext(source, {
        document: { querySelector: () => ({ addEventListener(name, handler) { handlers.set(`catalog:${name}`, handler); } }) },
        window: {
            scrollY: 640,
            addEventListener(name, handler) { handlers.set(`window:${name}`, handler); },
            requestAnimationFrame(callback) { callback(); },
            scrollTo(options) { scrolls.push(options.top); },
        },
        location, sessionStorage, URL,
    });
    return { handlers, storage, scrolls, location, sessionStorage };
}

test('returning to the original results restores the saved scroll position', () => {
    const app = setup();
    const returnTo = `${app.location.href}#catalog-product-42`;
    const link = { href: `http://localhost/eshop/produkt/hinge?return_to=${encodeURIComponent(returnTo)}` };
    app.handlers.get('catalog:click')({ button: 0, target: { closest: () => link } });
    app.handlers.get('window:pageshow')({ persisted: false });
    assert.deepEqual(app.scrolls, []);
    app.location.hash = 'catalog-product-42';
    app.handlers.get('window:pageshow')({ persisted: true });
    assert.deepEqual(app.scrolls, []);
    app.handlers.get('window:pageshow')({ persisted: false });
    assert.deepEqual(app.scrolls, [640]);
    assert.equal(app.storage.size, 0);
});

test('unrelated return URLs and unavailable storage leave native anchor navigation working', () => {
    const app = setup();
    app.location.hash = 'catalog-product-42';
    app.storage.set('catalog-product-return', JSON.stringify({ url: 'http://localhost/eshop/katalog#catalog-product-99', top: 640 }));
    app.handlers.get('window:pageshow')({ persisted: false });
    assert.deepEqual(app.scrolls, []);
    app.sessionStorage.getItem = () => { throw new Error('Storage blocked'); };
    app.sessionStorage.setItem = () => { throw new Error('Storage blocked'); };
    assert.doesNotThrow(() => app.handlers.get('window:pageshow')({ persisted: false }));
    const link = { href: 'http://localhost/eshop/produkt/hinge?return_to=%2Feshop%2Fkatalog' };
    assert.doesNotThrow(() => app.handlers.get('catalog:click')({ button: 0, target: { closest: () => link } }));
});