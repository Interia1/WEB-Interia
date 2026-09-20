(() => {
    const panel = document.querySelector('[data-assortment]');
    const trigger = document.querySelector('[data-assortment-trigger]');
    if (!panel || !trigger) return;
    const search = panel.querySelector('[data-assortment-search]');
    const categories = [...panel.querySelectorAll('[data-assortment-category]')];
    const catalogForm = document.querySelector('[data-catalog-form]');
    const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('sk');
    let openTimer;
    let closeTimer;
    let pinned = false;
    const updateSelection = facet => {
        const inputs = [...facet.querySelectorAll('input[name]')];
        const numbers = inputs.filter(input => input.type === 'number');
        const label = numbers.length
            ? numbers.filter(input => input.value !== '').map(input => `${input.name.endsWith('[min]') ? 'Od' : 'Do'} ${input.value}`).join(' · ') || 'Ľubovoľný rozsah'
            : `Vybrané: ${inputs.filter(input => input.checked).length}`;
        facet.querySelector('[data-facet-selection]').textContent = label;
    };
    const position = () => {
        const bounds = trigger.getBoundingClientRect();
        const width = Math.min(1180, document.documentElement.clientWidth - 24);
        const top = Math.max(12, Math.min(bounds.bottom + 8, window.innerHeight - 260));
        panel.style.width = `${width}px`;
        panel.style.left = `${Math.max(12, Math.min(bounds.left, document.documentElement.clientWidth - width - 12))}px`;
        panel.style.top = `${top}px`;
        panel.style.maxHeight = `${Math.max(0, window.innerHeight - top - 12)}px`;
    };
    const cancel = () => {
        clearTimeout(openTimer);
        clearTimeout(closeTimer);
    };
    const close = (restoreFocus = false) => {
        cancel();
        panel.hidden = true;
        pinned = false;
        trigger.setAttribute('aria-expanded', 'false');
        if (restoreFocus) trigger.focus();
    };
    const open = (pin = false) => {
        cancel();
        pinned ||= pin;
        if (panel.hidden) {
            const current = new URLSearchParams(new FormData(catalogForm));
            categories.forEach(category => {
                const slug = category.dataset.assortmentCategory;
                const link = category.querySelector('[data-assortment-link]');
                const url = new URL(catalogForm.action, location.href);
                url.search = '';
                url.searchParams.set('category', slug);
                url.searchParams.set('sort', current.get('sort') || 'recommended');
                if (current.get('q')) url.searchParams.set('q', current.get('q'));
                link.href = url.href;
                if (slug === current.get('category')) link.setAttribute('aria-current', 'true');
                else link.removeAttribute('aria-current');
                category.querySelectorAll('input[name]').forEach(input => {
                    if (['q', 'sort'].includes(input.name)) input.value = current.get(input.name) || '';
                    if (!input.name.startsWith('filters[')) return;
                    const values = slug === current.get('category') ? current.getAll(input.name) : [];
                    if (input.type === 'checkbox') input.checked = values.includes(input.value);
                    else input.value = values[0] || '';
                });
                category.querySelectorAll('[data-facet]').forEach(updateSelection);
            });
        }
        panel.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        position();
        if (pin) search.focus();
    };
    const leave = event => {
        if (event.pointerType !== 'mouse') return;
        clearTimeout(openTimer);
        closeTimer = setTimeout(() => {
            if (!pinned && !panel.contains(document.activeElement)) close();
        }, 240);
    };
    trigger.addEventListener('pointerenter', event => {
        if (event.pointerType !== 'mouse') return;
        cancel();
        openTimer = setTimeout(() => open(), 180);
    });
    trigger.addEventListener('pointerleave', leave);
    panel.addEventListener('pointerenter', cancel);
    panel.addEventListener('pointerleave', leave);
    trigger.addEventListener('click', () => {
        if (!panel.hidden && pinned) close(true);
        else open(true);
    });
    trigger.addEventListener('keydown', event => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            open(true);
        }
    });
    panel.querySelector('[data-assortment-close]').addEventListener('click', () => close(true));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !panel.hidden) {
            event.preventDefault();
            close(true);
        }
    });
    document.addEventListener('click', event => {
        if (!panel.contains(event.target) && !trigger.contains(event.target)) close();
    });
    document.addEventListener('focusin', event => {
        if (!panel.hidden && !panel.contains(event.target) && !trigger.contains(event.target)) close();
    });
    search.addEventListener('input', () => {
        const terms = normalize(search.value).trim().split(/\s+/);
        categories.forEach(category => {
            category.hidden = !terms.every(term => normalize(category.dataset.assortmentTerms).includes(term));
        });
        panel.querySelector('[data-assortment-empty]').hidden = categories.some(category => !category.hidden);
    });
    panel.addEventListener('input', event => {
        if (!event.target.matches('[data-facet-search]')) return;
        const term = normalize(event.target.value);
        event.target.closest('[data-facet]').querySelectorAll('[data-facet-option]').forEach(option => {
            option.hidden = !normalize(option.textContent).includes(term);
        });
    });
    panel.addEventListener('change', event => {
        const facet = event.target.closest('[data-facet]');
        if (facet) updateSelection(facet);
    });
    window.addEventListener('resize', () => { if (!panel.hidden) position(); });
    window.addEventListener('scroll', () => { if (!panel.hidden) position(); }, { passive: true });
    window.addEventListener('pagehide', () => close());
})();