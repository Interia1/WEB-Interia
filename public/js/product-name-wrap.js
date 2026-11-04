function wrapProductName(name, width, measure) {
    const characters = Array.from(name);
    const lines = [];
    let start = 0;

    while (start < characters.length) {
        let length = 0;
        while (length < 60 && start + length < characters.length
            && measure(characters.slice(start, start + length + 1).join('')) <= width) {
            length += 1;
        }
        length = Math.max(1, length);

        if (start + length < characters.length
            && !/\s/u.test(characters[start + length])
            && !/\s/u.test(characters[start + length - 1])) {
            const part = characters.slice(start, start + length);
            let space = part.length - 1;
            while (space > 0 && !/\s/u.test(part[space])) space -= 1;
            if (space > 0) length = space + 1;
        }

        lines.push(characters.slice(start, start + length).join(''));
        start += length;
    }

    return lines;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { wrapProductName };
} else {
    (() => {
        const catalog = document.querySelector('.shop-catalog');
        if (!catalog) return;

        const context = document.createElement('canvas').getContext('2d');
        if (!context) throw new Error('Product names cannot be measured without a canvas context.');

        let widths = new WeakMap();
        const render = heading => {
            const width = heading.getBoundingClientRect().width;
            if (width <= 0 || widths.get(heading) === width) return;
            widths.set(heading, width);

            const link = heading.querySelector('a');
            const name = link.getAttribute('aria-label');
            const style = window.getComputedStyle(heading);
            context.font = `${style.fontStyle} ${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
            const lines = wrapProductName(name, Math.max(0, width - 1), text => context.measureText(text).width);
            const fragment = document.createDocumentFragment();
            lines.forEach((line, index) => {
                if (index) {
                    const lineBreak = document.createElement('br');
                    lineBreak.setAttribute('aria-hidden', 'true');
                    fragment.append(lineBreak);
                }
                fragment.append(document.createTextNode(line));
            });
            link.replaceChildren(fragment);
        };

        const observer = typeof ResizeObserver === 'undefined'
            ? null
            : new ResizeObserver(entries => entries.forEach(entry => render(entry.target)));
        const refresh = () => {
            observer?.disconnect();
            widths = new WeakMap();
            catalog.querySelectorAll('.shop-product-name').forEach(heading => {
                render(heading);
                observer?.observe(heading);
            });
        };

        refresh();
        if (!observer) window.addEventListener('resize', refresh);
        const results = catalog.querySelector('[data-catalog-results]');
        if (results) new MutationObserver(refresh).observe(results, { childList: true });
        if (document.fonts) {
            document.fonts.ready.then(refresh);
            document.fonts.addEventListener('loadingdone', refresh);
        }
    })();
}
