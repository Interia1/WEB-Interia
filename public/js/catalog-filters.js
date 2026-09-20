(() => {
    const catalog = document.querySelector('[data-live-catalog]');
    if (!catalog) return;
    const form = catalog.querySelector('[data-catalog-form]');
    const results = catalog.querySelector('[data-catalog-results]');
    const facets = catalog.querySelector('[data-catalog-facets]');
    const active = catalog.querySelector('[data-catalog-active]');
    const filterPath = catalog.querySelector('[data-catalog-path]');
    const pathToggle = catalog.querySelector('[data-catalog-path-toggle]');
    const status = catalog.querySelector('[data-catalog-status]');
    const error = catalog.querySelector('[data-catalog-error]');
    const categoryBar = catalog.querySelector('[data-category-bar]');
    const connector = catalog.querySelector('[data-category-connector]');
    const categoryScroller = catalog.querySelector('[data-category-scroller]');
    const categoryTabs = catalog.querySelector('.shop-category-tabs');
    const categoryScrollStart = catalog.querySelector('[data-category-scroll-start]');
    const categoryScrollEnd = catalog.querySelector('[data-category-scroll-end]');
    const categoryPageStart = catalog.querySelector('[data-category-page-start]');
    const categoryPageEnd = catalog.querySelector('[data-category-page-end]');
    const categoryButtons = catalog.querySelectorAll('[data-catalog-category]');
    let categoryTrigger;
    let controller;
    let revision = 0;
    let timer;
    let categoryOpenTimer;
    let pendingCategory;
    let categoryPinned = false;
    let refreshFacets = false;
    let categoryDrag;
    let suppressCategoryClick = false;
    const tooltips = [];
    const hideTooltips = () => tooltips.forEach(tooltip => tooltip.hide());
    const disposeTooltips = () => {
        tooltips.forEach(tooltip => tooltip.dispose());
        tooltips.length = 0;
    };
    const initTooltips = () => {
        if (!window.bootstrap?.Tooltip) return;
        catalog.querySelectorAll('a[title], button[title]').forEach(element => {
            tooltips.push(new window.bootstrap.Tooltip(element, {
                container: 'body',
                customClass: 'shop-tooltip',
                placement: 'top',
                trigger: 'hover focus',
                delay: { show: 200, hide: 0 },
                html: false,
            }));
        });
    };

    const syncCategories = () => {
        categoryButtons.forEach(button => {
            const selected = button.dataset.catalogCategory === form.elements.category.value;
            if (selected) button.setAttribute('aria-current', 'true');
            else button.removeAttribute('aria-current');
            if (button.dataset.catalogCategory) {
                button.setAttribute('aria-expanded', String(selected && !facets.hidden));
            }
        });
    };
    const closeCategory = (restoreFocus = false) => {
        clearTimeout(categoryOpenTimer);
        pendingCategory = undefined;
        categoryPinned = false;
        facets.hidden = true;
        connector.hidden = true;
        syncCategories();
        if (restoreFocus) categoryTrigger?.focus();
    };
    closeCategory();

    const positionConnector = () => {
        connector.hidden = true;
        if (facets.hidden || !categoryTrigger || !window.matchMedia?.('(min-width: 992px)').matches) return;
        const bar = categoryBar.getBoundingClientRect();
        const tabs = categoryTabs.getBoundingClientRect();
        const button = categoryTrigger.getBoundingClientRect();
        const panel = facets.getBoundingClientRect();
        const start = button.left + button.width / 2;
        if (start < tabs.left || start > tabs.right) return;
        const end = panel.left + panel.width / 2;
        const lane = (button.bottom + bar.bottom) / 2;
        const coordinates = {
            'start-x': start - bar.left,
            'start-y': button.bottom - bar.top,
            'start-height': Math.max(0, lane - button.bottom),
            left: Math.min(start, end) - bar.left,
            lane: lane - bar.top,
            width: Math.abs(end - start),
            'end-x': end - bar.left,
            'end-height': Math.max(0, panel.top - lane),
        };
        for (const [name, value] of Object.entries(coordinates)) {
            connector.style.setProperty(`--connector-${name}`, `${value}px`);
        }
        connector.hidden = false;
    };
    const syncCategoryScroller = () => {
        const maximum = Math.max(0, categoryTabs.scrollWidth - categoryTabs.clientWidth);
        const overflowing = maximum > 1;
        const progress = overflowing ? Math.max(0, Math.min(1, categoryTabs.scrollLeft / maximum)) : 0.5;
        categoryScroller.style.setProperty('--category-start-tint', String(progress));
        categoryScroller.style.setProperty('--category-end-tint', String(1 - progress));
        const atStart = categoryTabs.scrollLeft <= 1;
        const atEnd = categoryTabs.scrollLeft >= maximum - 1;
        categoryScroller.dataset.categoryOverflow = String(overflowing);
        categoryScroller.dataset.categoryStart = String(atStart);
        categoryScroller.dataset.categoryEnd = String(atEnd);
        categoryScrollStart.disabled = !overflowing || atStart;
        categoryScrollEnd.disabled = !overflowing || atEnd;
        categoryPageStart.disabled = categoryScrollStart.disabled;
        categoryPageEnd.disabled = categoryScrollEnd.disabled;
    };
    const resizeCategory = () => {
        const scrollTop = window.scrollY;
        const catalogTop = catalog.getBoundingClientRect().top + scrollTop;
        catalog.style.setProperty('--catalog-min-height', `${Math.max(0, window.innerHeight - catalogTop)}px`);
        const heading = catalog.querySelector('.shop-catalog-heading');
        const categoryTitle = catalog.querySelector('.shop-category-title');
        const titleBounds = categoryTitle.getBoundingClientRect();
        if (titleBounds.width > 0) {
            catalog.style.setProperty('--catalog-assortment-top', `${Math.max(0, titleBounds.top - heading.getBoundingClientRect().top)}px`);
        }
        const productList = catalog.querySelector('.shop-product-list');
        const listTop = productList ? productList.getBoundingClientRect().top + scrollTop : window.innerHeight;
        catalog.style.setProperty('--catalog-product-row-height', `${Math.max(0, (window.innerHeight - listTop - 8) / 6)}px`);
        if (!facets.hidden && window.matchMedia?.('(min-width: 992px)').matches) {
            const panelOffset = results.getBoundingClientRect().top - categoryBar.getBoundingClientRect().top;
            catalog.style.setProperty('--catalog-filter-top', `${panelOffset}px`);
        }
        syncCategoryScroller();
        positionConnector();
        if (facets.hidden) return;
        const viewport = window.visualViewport;
        const bottom = viewport ? viewport.offsetTop + viewport.height : window.innerHeight;
        const panelTop = facets.getBoundingClientRect().top + scrollTop;
        const height = Math.max(0, bottom - panelTop - 12);
        catalog.style.setProperty('--catalog-filter-height', `${height}px`);
    };
    pathToggle.checked = true;
    try {
        pathToggle.checked = window.localStorage.getItem('catalog-filter-path-visible') !== 'false';
    } catch {}
    active.hidden = !pathToggle.checked;
    let pathHovered = false;
    const releasePathHover = () => {
        pathHovered = false;
        filterPath.removeAttribute('data-path-hover');
        active.style.minHeight = '';
    };
    filterPath.addEventListener('pointerenter', event => {
        if (event.pointerType !== 'mouse') return;
        pathHovered = true;
        filterPath.setAttribute('data-path-hover', '');
        filterPath.open = true;
        active.style.minHeight = `${active.getBoundingClientRect().height || 0}px`;
    });
    filterPath.addEventListener('pointerleave', event => {
        if (event.pointerType !== 'mouse') return;
        releasePathHover();
        filterPath.open = false;
    });
    filterPath.addEventListener('focusout', event => {
        if (!pathHovered && !filterPath.contains(event.relatedTarget)) filterPath.open = false;
    });
    filterPath.addEventListener('click', event => {
        if (pathHovered && filterPath.open && event.target.closest('summary')) event.preventDefault();
    });
    filterPath.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            filterPath.querySelector('summary').focus();
            releasePathHover();
            filterPath.open = false;
        }
    });
    resizeCategory();
    window.addEventListener('resize', resizeCategory);
    window.addEventListener('scroll', () => {
        resizeCategory();
        hideTooltips();
    }, { passive: true });
    catalog.addEventListener('scroll', hideTooltips, true);
    window.visualViewport?.addEventListener('resize', resizeCategory);
    window.visualViewport?.addEventListener('scroll', resizeCategory);
    categoryTabs.addEventListener('scroll', () => {
        syncCategoryScroller();
        resetHiddenCategory();
        if (categoryPinned) positionConnector();
    }, { passive: true });
    const scrollCategories = direction => {
        categoryTabs.scrollBy({ left: direction * 64, behavior: 'smooth' });
    };
    const stopArrowScrolling = [];
    for (const [button, direction] of [[categoryScrollStart, -1], [categoryScrollEnd, 1]]) {
        let frame;
        let previousTime;
        let moved = false;
        const stop = () => {
            window.cancelAnimationFrame(frame);
            frame = undefined;
            previousTime = undefined;
        };
        const advance = timestamp => {
            if (previousTime === undefined) previousTime = timestamp;
            const elapsed = Math.min(32, timestamp - previousTime);
            previousTime = timestamp;
            const maximum = Math.max(0, categoryTabs.scrollWidth - categoryTabs.clientWidth);
            const before = categoryTabs.scrollLeft;
            categoryTabs.scrollLeft = Math.max(0, Math.min(maximum, before + direction * elapsed * 0.3));
            moved ||= categoryTabs.scrollLeft !== before;
            syncCategoryScroller();
            positionConnector();
            if (button.disabled) stop();
            else frame = window.requestAnimationFrame(advance);
        };
        button.addEventListener('pointerdown', event => {
            if (event.button !== 0 || button.disabled) return;
            stopArrowScrolling.forEach(cancel => cancel());
            clearTimeout(categoryOpenTimer);
            pendingCategory = undefined;
            hideTooltips();
            moved = false;
            frame = window.requestAnimationFrame(advance);
        });
        button.addEventListener('pointerleave', stop);
        button.addEventListener('pointercancel', stop);
        button.addEventListener('click', event => {
            stop();
            if (!moved || event?.detail === 0) scrollCategories(direction);
            moved = false;
        });
        button.addEventListener('dblclick', event => {
            event.preventDefault();
            stopArrowScrolling.forEach(cancel => cancel());
            clearTimeout(categoryOpenTimer);
            pendingCategory = undefined;
            hideTooltips();
            moved = false;
            categoryTabs.scrollTo({
                left: direction < 0 ? 0 : Math.max(0, categoryTabs.scrollWidth - categoryTabs.clientWidth),
                behavior: 'smooth',
            });
        });
        stopArrowScrolling.push(stop);
    }
    for (const [button, direction] of [[categoryPageStart, -1], [categoryPageEnd, 1]]) {
        button.addEventListener('click', () => {
            if (button.disabled) return;
            stopArrowScrolling.forEach(stop => stop());
            clearTimeout(categoryOpenTimer);
            pendingCategory = undefined;
            hideTooltips();
            const tabs = categoryTabs.getBoundingClientRect();
            const width = categoryTabs.clientWidth;
            const centers = [...categoryButtons].map(category => category.getBoundingClientRect())
                .filter(rect => direction > 0
                    ? rect.right <= tabs.right && rect.left + rect.width / 2 > tabs.left
                    : rect.left >= tabs.left && rect.left + rect.width / 2 < tabs.right)
                .map(rect => rect.left + rect.width / 2 - tabs.left);
            const distance = centers.length
                ? (direction > 0 ? Math.max(...centers) : width - Math.min(...centers))
                : width / 2;
            const maximum = Math.max(0, categoryTabs.scrollWidth - width);
            categoryTabs.scrollTo({
                left: Math.max(0, Math.min(maximum, categoryTabs.scrollLeft + direction * distance)),
                behavior: 'smooth',
            });
        });
    }
    window.addEventListener('pointerup', event => {
        stopArrowScrolling.forEach(stop => stop());
        if (categoryDrag) finishCategoryDrag(event);
    });
    window.addEventListener('pointercancel', event => {
        stopArrowScrolling.forEach(stop => stop());
        if (categoryDrag) finishCategoryDrag(event);
    });
    window.addEventListener('blur', () => {
        stopArrowScrolling.forEach(stop => stop());
        if (categoryDrag) finishCategoryDrag(categoryDrag);
    });
    categoryTabs.addEventListener('pointerdown', event => {
        if (event.pointerType !== 'mouse' || event.button !== 0) return;
        suppressCategoryClick = false;
        categoryDrag = { pointerId: event.pointerId, startX: event.clientX, startScroll: categoryTabs.scrollLeft, moved: false };
    });
    categoryTabs.addEventListener('pointermove', event => {
        if (!categoryDrag || event.pointerId !== categoryDrag.pointerId) return;
        const distance = event.clientX - categoryDrag.startX;
        if (!categoryDrag.moved && Math.abs(distance) > 3) {
            categoryDrag.moved = true;
            categoryTabs.setPointerCapture?.(event.pointerId);
            categoryTabs.dataset.categoryDragging = 'true';
            clearTimeout(categoryOpenTimer);
            pendingCategory = undefined;
            hideTooltips();
        }
        if (!categoryDrag.moved) return;
        event.preventDefault();
        categoryTabs.scrollLeft = categoryDrag.startScroll - distance;
        syncCategoryScroller();
        positionConnector();
    });
    const finishCategoryDrag = event => {
        if (!categoryDrag || event.pointerId !== categoryDrag.pointerId) return;
        suppressCategoryClick = categoryDrag.moved;
        if (categoryDrag.moved) categoryTabs.releasePointerCapture?.(event.pointerId);
        categoryTabs.removeAttribute('data-category-dragging');
        categoryDrag = undefined;
    };
    categoryTabs.addEventListener('pointerup', finishCategoryDrag);
    categoryTabs.addEventListener('pointercancel', finishCategoryDrag);
    categoryTabs.addEventListener('dragstart', event => event.preventDefault());

    const updateSelection = facet => {
        if (!facet) return;
        const numbers = [...facet.querySelectorAll('input[type="number"]')];
        const checked = facet.querySelectorAll('input[type="checkbox"]:checked');
        const label = numbers.length
            ? numbers.filter(input => input.value !== '').map(input => `${input.name.endsWith('[min]') ? 'Od' : 'Do'} ${input.value}`).join(' · ') || 'Ľubovoľný rozsah'
            : `Vybrané: ${checked.length}`;
        facet.querySelector('[data-facet-selection]').textContent = label;
    };
    const hasActiveCategoryFilters = () => form.elements.q.value !== '' || [...facets.querySelectorAll('input[name]')].some(input =>
        input.name?.startsWith('filters[') && (input.type === 'checkbox' ? input.checked : input.value !== '')
    );
    document.addEventListener('click', event => {
        if (!categoryBar.contains(event.target) && !filterPath.contains(event.target)) closeCategory();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') hideTooltips();
        if (event.key === 'Escape') closeCategory(!facets.hidden && categoryBar.contains(document.activeElement));
    });
    document.addEventListener('scroll', event => {
        if (event.target.matches?.('.shop-category-tabs')) {
            if (!categoryPinned) closeCategory();
            else positionConnector();
        }
    }, true);

    const invalidate = () => {
        clearTimeout(timer);
        controller?.abort();
        revision += 1;
    };
    const formUrl = () => {
        const url = new URL(form.action, location.href);
        for (const [name, value] of new FormData(form)) {
            if (value !== '') url.searchParams.append(name, value);
        }
        return url;
    };
    const syncForm = url => {
        const category = url.searchParams.get('category') || '';
        if (form.elements.category.value !== category) {
            facets.innerHTML = '';
            refreshFacets = true;
        }
        form.elements.q.value = url.searchParams.get('q') || '';
        form.elements.category.value = category;
        form.elements.sort.value = url.searchParams.get('sort') || 'recommended';
        syncCategories();
        facets.querySelectorAll('input[name]').forEach(input => {
            if (input.type === 'checkbox') {
                input.checked = [...url.searchParams].some(([name, value]) => name.replace(/\[\d+\]$/, '[]') === input.name && value === input.value);
            } else {
                input.value = url.searchParams.get(input.name) || '';
            }
        });
        facets.querySelectorAll('[data-facet]').forEach(updateSelection);
    };
    const load = async (url, replaceFacets = false, historyMode = 'push') => {
        invalidate();
        disposeTooltips();
        const requestRevision = revision;
        controller = new AbortController();
        results.setAttribute('aria-busy', 'true');
        status.textContent = 'Načítavam…';
        error.hidden = true;
        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
                signal: controller.signal,
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : 'Produkty sa nepodarilo načítať. Skúste znova.');
            }
            if (requestRevision !== revision) return;
            results.innerHTML = data.results;
            active.innerHTML = data.active;
            active.hidden = !pathToggle.checked;
            if (replaceFacets || refreshFacets) {
                facets.innerHTML = data.filters;
                refreshFacets = false;
            }
            catalog.querySelector('[data-catalog-total]').textContent = `${data.total} položiek`;
            status.textContent = `${data.total} nájdených produktov`;
            initTooltips();
            resizeCategory();
            if (historyMode === 'push' && url.href !== location.href) {
                history.pushState(null, '', url);
                window.dispatchEvent(new Event('catalog:historychange'));
            }
        } catch (exception) {
            if (exception.name === 'AbortError' || requestRevision !== revision) return;
            error.textContent = exception.message;
            error.hidden = false;
            status.textContent = '';
            active.hidden = !pathToggle.checked;
            initTooltips();
        } finally {
            if (requestRevision === revision) results.setAttribute('aria-busy', 'false');
        }
    };
    const resetHiddenCategory = () => {
        const category = form.elements.category.value;
        if (!category) return;
        const selected = [...categoryButtons].find(button => button.dataset.catalogCategory === category);
        if (!selected) return;
        const tabs = categoryTabs.getBoundingClientRect();
        const button = selected.getBoundingClientRect();
        if (tabs.width <= 0 || button.width <= 0) return;
        if (button.right > tabs.left && button.left < tabs.right) return;
        const url = new URL(form.action, location.href);
        url.search = '';
        if (form.elements.q.value) url.searchParams.set('q', form.elements.q.value);
        url.searchParams.set('sort', form.elements.sort.value);
        releasePathHover();
        filterPath.open = false;
        active.innerHTML = '';
        closeCategory();
        categoryTrigger = undefined;
        syncForm(url);
        resizeCategory();
        load(url, true);
    };
    const openCategory = (link, pin = false) => {
        clearTimeout(categoryOpenTimer);
        pendingCategory = undefined;
        const category = link.dataset.catalogCategory;
        const sameCategory = category === form.elements.category.value;
        if (!pin && !sameCategory && [...facets.querySelectorAll('input[name]')].some(input =>
            input.name?.startsWith('filters[') && (input.type === 'checkbox' ? input.checked : input.value !== '')
        )) return;
        categoryTrigger = link;
        categoryPinned = pin && Boolean(category);
        facets.hidden = !category;
        if (!sameCategory) {
            facets.innerHTML = '';
            form.elements.category.value = category;
            refreshFacets = true;
        }
        syncCategories();
        resizeCategory();
        if (!sameCategory || refreshFacets) load(formUrl(), true);
    };
    catalog.addEventListener('pointerover', event => {
        if (event.pointerType !== 'mouse' || categoryDrag?.moved) return;
        const link = event.target.closest('[data-catalog-category]');
        if (link?.dataset.catalogCategory === undefined || link.contains(event.relatedTarget)) return;
        clearTimeout(categoryOpenTimer);
        pendingCategory = undefined;
        if (link === categoryTrigger && !facets.hidden) return;
        pendingCategory = link;
        categoryOpenTimer = setTimeout(() => openCategory(link), 400);
    });
    catalog.addEventListener('pointerout', event => {
        if (event.pointerType !== 'mouse' || !pendingCategory || pendingCategory.contains(event.relatedTarget)) return;
        if (event.target.closest?.('[data-catalog-category]') !== pendingCategory) return;
        clearTimeout(categoryOpenTimer);
        pendingCategory = undefined;
    });
    form.addEventListener('submit', event => {
        event.preventDefault();
        load(formUrl());
    });
    form.addEventListener('input', event => {
        if (event.target.matches('[data-facet-search]')) {
            const query = event.target.value.trim().toLocaleLowerCase('sk');
            event.target.closest('[data-facet]').querySelectorAll('[data-facet-option]').forEach(option => {
                option.hidden = !option.textContent.toLocaleLowerCase('sk').includes(query);
            });
            return;
        }
        if (!event.target.matches('input[type="search"], input[type="number"]')) return;
        updateSelection(event.target.closest('[data-facet]'));
        invalidate();
        timer = setTimeout(() => load(formUrl()), 250);
    });
    form.addEventListener('change', event => {
        if (event.target === pathToggle) {
            active.hidden = !pathToggle.checked;
            try {
                window.localStorage.setItem('catalog-filter-path-visible', String(pathToggle.checked));
            } catch {}
            resizeCategory();
            return;
        }
        if (!event.target.matches('select, input[type="checkbox"]')) return;
        updateSelection(event.target.closest('[data-facet]'));
        if (event.target.name === 'category') {
            facets.innerHTML = '';
            refreshFacets = true;
        }
        load(formUrl());
    });
    catalog.addEventListener('click', event => {
        hideTooltips();
        const link = event.target.closest('a[data-catalog-link]');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
        if (suppressCategoryClick) {
            event.preventDefault();
            suppressCategoryClick = false;
            return;
        }
        event.preventDefault();
        if (link.dataset?.catalogCategory !== undefined) {
            if (link.dataset.catalogCategory && link.dataset.catalogCategory === form.elements.category.value && hasActiveCategoryFilters()) {
                const url = new URL(form.action, location.href);
                url.search = '';
                url.searchParams.set('category', link.dataset.catalogCategory);
                url.searchParams.set('sort', form.elements.sort.value);
                releasePathHover();
                filterPath.open = false;
                active.hidden = true;
                closeCategory();
                syncForm(url);
                resizeCategory();
                load(url, true);
                return;
            }
            if (link.dataset.catalogCategory && link.dataset.catalogCategory === form.elements.category.value && !facets.hidden) {
                closeCategory();
                return;
            }
            openCategory(link, true);
            return;
        }
        if (link.dataset?.catalogClearFilters !== undefined) {
            releasePathHover();
            filterPath.open = false;
            active.hidden = true;
        }
        const url = new URL(link.href, location.href);
        const filterControl = filterPath.contains(link) || (facets.contains(link) && link.dataset?.catalogClearFilters !== undefined);
        if (!filterControl || (url.searchParams.get('category') || '') !== form.elements.category.value) {
            closeCategory();
        }
        syncForm(url);
        load(url, true);
    });
    const restoreReturnedCategory = () => {
        const url = new URL(location.href);
        if (!/^#catalog-product-\d+$/.test(url.hash)) return;
        const category = form.elements.category.value;
        if (!category || url.searchParams.get('category') !== category) return;
        const button = [...categoryButtons].find(button => button.dataset.catalogCategory === category);
        if (!button) return;
        clearTimeout(categoryOpenTimer);
        pendingCategory = undefined;
        categoryTrigger = button;
        categoryPinned = true;
        facets.hidden = false;
        syncCategories();
        resizeCategory();
    };
    restoreReturnedCategory();
    initTooltips();
    window.addEventListener('pageshow', restoreReturnedCategory);
    window.addEventListener('popstate', () => {
        closeCategory();
        const url = new URL(location.href);
        syncForm(url);
        restoreReturnedCategory();
        load(url, true, 'none');
    });
})();