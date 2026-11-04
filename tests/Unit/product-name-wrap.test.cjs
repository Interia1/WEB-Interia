const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');
const { wrapProductName } = require('../../public/js/product-name-wrap.js');

test('natural wrapping uses both lines before the sixty-character limit', () => {
    const name = 'Ukážka: hliníkový konštrukčný profil pre moderné interiérové systémy so zvýšenou pevnosťou';
    const lines = wrapProductName(name, 52, text => Array.from(text).length);

    assert.equal(lines.length, 2);
    assert.equal(lines.join(''), name);
    assert.ok(lines.every(line => Array.from(line).length <= 52));
    assert.match(lines[1], /pevnosťou$/);
});

test('each line respects sixty characters and prefers word boundaries', () => {
    const name = 'Ukážka: oceľový montážny prvok pre nosné konštrukcie s ochrannou povrchovou úpravou';
    const lines = wrapProductName(name, 100, text => Array.from(text).length);

    assert.deepEqual(lines, [
        'Ukážka: oceľový montážny prvok pre nosné konštrukcie s ',
        'ochrannou povrchovou úpravou',
    ]);
    assert.equal(lines.join(''), name);
    assert.ok(lines.every(line => Array.from(line).length <= 60));
});

test('long words and multibyte names remain intact within the limit', () => {
    const name = 'á'.repeat(121);
    const lines = wrapProductName(name, 100, text => Array.from(text).length);

    assert.deepEqual(lines.map(line => Array.from(line).length), [60, 60, 1]);
    assert.equal(lines.join(''), name);
});

test('narrow widths still make progress', () => {
    assert.deepEqual(wrapProductName('abc', 0, text => text.length), ['a', 'b', 'c']);
});

test('catalog names rewrap after resizing and AJAX replacement', () => {
    const source = readFileSync(new URL('../../public/js/product-name-wrap.js', `file://${__filename}`), 'utf8');
    let resize;
    let mutation;
    const results = {};
    const makeHeading = name => {
        const link = {
            lines: [],
            getAttribute() { return name; },
            replaceChildren(fragment) {
                this.lines = [''];
                fragment.children.forEach(child => {
                    if (child.type === 'br') this.lines.push('');
                    else this.lines[this.lines.length - 1] += child.text;
                });
            },
        };
        return {
            width: 53,
            link,
            getBoundingClientRect() { return { width: this.width }; },
            querySelector() { return link; },
        };
    };
    const name = 'Ukážka: hliníkový konštrukčný profil pre moderné interiérové systémy so zvýšenou pevnosťou';
    const first = makeHeading(name);
    let headings = [first];
    const catalog = {
        querySelectorAll() { return headings; },
        querySelector() { return results; },
    };
    const document = {
        querySelector() { return catalog; },
        createElement(tag) {
            if (tag === 'canvas') return { getContext: () => ({ measureText: text => ({ width: Array.from(text).length }) }) };
            return { type: tag, setAttribute() {} };
        },
        createDocumentFragment() { return { children: [], append(child) { this.children.push(child); } }; },
        createTextNode(text) { return { type: 'text', text }; },
    };
    vm.runInNewContext(source, {
        document,
        window: { getComputedStyle: () => ({ fontStyle: 'normal', fontWeight: '700', fontSize: '15px', fontFamily: 'Arial' }) },
        ResizeObserver: class {
            constructor(callback) { resize = callback; }
            observe() {}
            disconnect() {}
        },
        MutationObserver: class {
            constructor(callback) { mutation = callback; }
            observe(target, options) {
                assert.equal(target, results);
                assert.equal(options.childList, true);
            }
        },
    });

    assert.equal(first.link.lines.length, 2);
    assert.equal(first.link.lines.join(''), name);
    first.width = 81;
    resize([{ target: first }]);
    assert.ok(first.link.lines.every(line => Array.from(line).length <= 60));
    assert.equal(first.link.lines.join(''), name);

    const second = makeHeading(name);
    headings = [second];
    mutation();
    assert.equal(second.link.lines.length, 2);
    assert.equal(second.link.lines.join(''), name);
});
