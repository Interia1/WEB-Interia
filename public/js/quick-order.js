(() => {
    const assistant = document.querySelector('[data-home-assistant]');

    if (!assistant) {
        return;
    }

    const form = assistant.querySelector('[data-assistant-form]');
    const input = assistant.querySelector('[data-assistant-input]');
    const results = assistant.querySelector('[data-assistant-results]');
    const list = assistant.querySelector('[data-assistant-list]');
    const heading = assistant.querySelector('[data-assistant-heading]');
    const options = Array.from(assistant.querySelectorAll('[data-assistant-option]'));
    const productSearchUrl = assistant.dataset.productSearchUrl;

    if (!form || !input || !results || !list || !heading || !productSearchUrl) {
        return;
    }

    const normalize = (value) => value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();

    const destinations = options.map((option, index) => ({
        url: option.href,
        title: option.dataset.title,
        description: option.dataset.description,
        icon: option.dataset.icon,
        keywords: normalize(`${option.dataset.title} ${option.dataset.description} ${option.dataset.keywords}`),
        index,
    }));
    let visibleDestinations = [];
    let searchTimer;
    let searchController;
    let searchVersion = 0;

    const cancelSearch = () => {
        window.clearTimeout(searchTimer);
        searchController?.abort();
        searchVersion += 1;
    };

    const scoreDestination = (destination, query) => {
        const terms = normalize(query).split(/\s+/).filter((term) => term.length > 1);

        return terms.reduce((score, term) => {
            if (destination.keywords.includes(term)) {
                return score + (normalize(destination.title).includes(term) ? 4 : 2);
            }

            return score;
        }, 0);
    };

    const setOpen = (isOpen) => {
        results.hidden = !isOpen;
        input.setAttribute('aria-expanded', String(isOpen));

        if (!isOpen) {
            cancelSearch();
        }
    };

    const renderResults = (products = [], status = '') => {
        const query = input.value.trim();
        const ranked = destinations
            .map((destination) => ({ ...destination, score: scoreDestination(destination, query) }))
            .sort((first, second) => second.score - first.score || first.index - second.index);

        visibleDestinations = ranked.filter((destination) => destination.score > 0).slice(0, 3);

        visibleDestinations = [
            ...products.map((product) => ({
                url: product.url,
                title: product.name,
                description: product.description,
                icon: 'bi-box-seam',
            })),
            ...visibleDestinations,
        ];

        heading.textContent = status || (visibleDestinations.length > 0
            ? 'Výsledky vyhľadávania'
            : 'Nenašli sa žiadne výsledky');
        list.replaceChildren(...visibleDestinations.map((destination) => {
            const link = document.createElement('a');
            const icon = document.createElement('i');
            const copy = document.createElement('span');
            const title = document.createElement('strong');
            const description = document.createElement('small');
            const arrow = document.createElement('i');

            link.href = destination.url;
            link.className = 'home-assistant-result';
            icon.className = `bi ${destination.icon}`;
            icon.setAttribute('aria-hidden', 'true');
            title.textContent = destination.title;
            description.textContent = destination.description;
            arrow.className = 'bi bi-arrow-right-short home-assistant-result-arrow';
            arrow.setAttribute('aria-hidden', 'true');
            copy.append(title, description);
            link.append(icon, copy, arrow);

            return link;
        }));
        setOpen(true);
    };

    const updateResults = () => {
        cancelSearch();
        const query = input.value.trim();

        if (query === '') {
            visibleDestinations = [];
            list.replaceChildren();
            setOpen(false);
            return;
        }

        const version = searchVersion;
        const shouldSearch = productSearchUrl && Array.from(query).length >= 2;

        renderResults([], shouldSearch ? 'Hľadám produkty…' : '');

        if (!shouldSearch) {
            return;
        }

        searchTimer = window.setTimeout(async () => {
            searchController = new AbortController();
            const searchUrl = new URL(productSearchUrl, window.location.origin);
            searchUrl.searchParams.set('q', query);

            try {
                const response = await fetch(searchUrl, {
                    headers: { Accept: 'application/json' },
                    signal: searchController.signal,
                });

                if (!response.ok) {
                    throw new Error('Product search failed');
                }

                const data = await response.json();

                if (version !== searchVersion) {
                    return;
                }

                renderResults(data.products);
            } catch (error) {
                if (error.name !== 'AbortError' && version === searchVersion) {
                    renderResults([], 'Produkty sa nepodarilo načítať. Skúste to znova.');
                }
            }
        }, 250);
    };

    input.addEventListener('focus', updateResults);
    input.addEventListener('input', updateResults);
    assistant.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            input.focus();
            setOpen(false);
        }

        if (event.key === 'ArrowDown' && event.target === input && !results.hidden) {
            event.preventDefault();
            list.querySelector('a')?.focus();
        }
    });
    form.addEventListener('submit', (event) => {
        event.preventDefault();

        if (input.value.trim() !== '' && visibleDestinations[0]) {
            cancelSearch();
            window.location.assign(visibleDestinations[0].url);
        }
    });
    document.addEventListener('click', (event) => {
        if (!assistant.contains(event.target)) {
            setOpen(false);
        }
    });
})();

(() => {
    const tiles = Array.from(document.querySelectorAll('.quick-order-tile'));
    const detailsElements = tiles
        .map((tile) => tile.querySelector('.quick-order-more'))
        .filter(Boolean);

    detailsElements.forEach((details) => {
        details.addEventListener('toggle', () => {
            details.closest('.quick-order-tile')?.classList.toggle('is-expanded', details.open);

            if (!details.open) {
                return;
            }

            detailsElements.forEach((otherDetails) => {
                if (otherDetails !== details) {
                    otherDetails.open = false;
                }
            });
        });
    });

    tiles.forEach((tile) => {
        const details = tile.querySelector('.quick-order-more');
        const summary = details?.querySelector('.quick-order-toggle');
        let closeTimer = null;

        if (!details || !summary) {
            return;
        }

        summary.addEventListener('mouseenter', () => {
            details.open = true;
        });

        tile.addEventListener('mouseenter', () => {
            if (closeTimer !== null) {
                window.clearTimeout(closeTimer);
                closeTimer = null;
            }
        });

        tile.addEventListener('mouseleave', () => {
            closeTimer = window.setTimeout(() => {
                details.open = false;
                closeTimer = null;
            }, 500);
        });
    });
})();

(() => {
    const manager = document.getElementById('homeShortcutManager');
    const modalElement = document.getElementById('shortcutModal');

    if (!manager || !modalElement || typeof bootstrap === 'undefined') {
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const errorElement = modalElement.querySelector('[data-shortcut-error]');
    const saveButton = modalElement.querySelector('[data-save-shortcuts]');
    const groupNames = ['eshop', 'semifinished', 'custom'];
    const definitions = {};

    groupNames.forEach((groupName) => {
        definitions[groupName] = {};
        modalElement.querySelectorAll(`[data-shortcut-options="${groupName}"] input`).forEach((input) => {
            definitions[groupName][input.value] = {
                label: input.dataset.label,
                icon: input.dataset.icon,
                url: input.dataset.url,
            };
        });
    });

    const parsePreferences = (value) => {
        try {
            const parsed = JSON.parse(value);
            const preferences = {};

            groupNames.forEach((groupName) => {
                const selected = Array.isArray(parsed?.[groupName]) ? parsed[groupName] : [];
                preferences[groupName] = selected.filter((key, index) => (
                    definitions[groupName][key] && selected.indexOf(key) === index
                )).slice(0, 5);

                if (preferences[groupName].length < 1) {
                    throw new Error('Invalid shortcut selection');
                }
            });

            return preferences;
        } catch {
            return null;
        }
    };

    const serverPreferences = parsePreferences(manager.dataset.preferences);
    const localPreferences = manager.dataset.saveUrl
        ? null
        : parsePreferences(localStorage.getItem(manager.dataset.storageKey));
    let currentPreferences = localPreferences || serverPreferences;

    if (!currentPreferences) {
        return;
    }

    const render = () => {
        groupNames.forEach((groupName) => {
            const list = manager.querySelector(`[data-shortcut-group="${groupName}"] .quick-shortcuts-list`);

            if (!list) {
                return;
            }

            list.replaceChildren(...currentPreferences[groupName].map((key) => {
                const shortcut = definitions[groupName][key];
                const link = document.createElement('a');
                const icon = document.createElement('i');
                const label = document.createElement('span');

                link.href = shortcut.url;
                link.className = 'quick-shortcut-link';
                link.dataset.shortcutKey = key;
                link.title = shortcut.label;
                icon.className = `bi ${shortcut.icon}`;
                icon.setAttribute('aria-hidden', 'true');
                label.textContent = shortcut.label;
                link.append(icon, label);

                return link;
            }));
        });
    };

    const syncInputs = () => {
        groupNames.forEach((groupName) => {
            modalElement.querySelectorAll(`[data-shortcut-options="${groupName}"] input`).forEach((input) => {
                input.checked = currentPreferences[groupName].includes(input.value);
            });
        });
    };

    const showError = (message) => {
        errorElement.textContent = message;
        errorElement.classList.remove('d-none');
    };

    manager.querySelectorAll('[data-edit-shortcuts]').forEach((button) => {
        button.addEventListener('click', () => {
            syncInputs();
            errorElement.classList.add('d-none');
            bootstrap.Tab.getOrCreateInstance(document.getElementById(`shortcut-tab-${button.dataset.editShortcuts}`)).show();
            modal.show();
        });
    });

    saveButton?.addEventListener('click', async () => {
        const nextPreferences = {};

        for (const groupName of groupNames) {
            nextPreferences[groupName] = Array.from(
                modalElement.querySelectorAll(`[data-shortcut-options="${groupName}"] input:checked`),
                (input) => input.value,
            );

            if (nextPreferences[groupName].length < 1 || nextPreferences[groupName].length > 5) {
                bootstrap.Tab.getOrCreateInstance(document.getElementById(`shortcut-tab-${groupName}`)).show();
                showError('V každej časti vyberte 1 až 5 skratiek.');
                return;
            }
        }

        saveButton.disabled = true;
        errorElement.classList.add('d-none');

        try {
            if (manager.dataset.saveUrl) {
                const response = await fetch(manager.dataset.saveUrl, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': manager.dataset.csrfToken,
                    },
                    body: JSON.stringify({ shortcuts: nextPreferences }),
                });

                if (!response.ok) {
                    throw new Error('Save failed');
                }
            } else {
                localStorage.setItem(manager.dataset.storageKey, JSON.stringify(nextPreferences));
            }

            currentPreferences = nextPreferences;
            render();
            modal.hide();
        } catch {
            showError('Skratky sa nepodarilo uložiť. Skúste to znova.');
        } finally {
            saveButton.disabled = false;
        }
    });

    render();
})();