(() => {
    const catalog = document.querySelector('[data-live-catalog]');
    if (!catalog) return;
    const storageKey = 'catalog-product-return';
    const rememberPosition = event => {
        const link = event.target.closest('a[data-product-link]');
        if (!link || event.button > 1) return;
        const returnTo = new URL(link.href, location.href).searchParams.get('return_to');
        if (!returnTo) return;
        try {
            sessionStorage.setItem(storageKey, JSON.stringify({ url: new URL(returnTo, location.href).href, top: window.scrollY }));
        } catch {}
    };
    catalog.addEventListener('click', rememberPosition, true);
    catalog.addEventListener('auxclick', rememberPosition, true);
    window.addEventListener('pageshow', event => {
        if (event.persisted || !/^#catalog-product-\d+$/.test(location.hash)) return;
        try {
            const saved = JSON.parse(sessionStorage.getItem(storageKey));
            if (!saved || saved.url !== location.href || !Number.isFinite(saved.top) || saved.top < 0) return;
            window.requestAnimationFrame(() => {
                window.scrollTo({ top: saved.top, left: 0, behavior: 'instant' });
            });
            sessionStorage.removeItem(storageKey);
        } catch {}
    });
})();