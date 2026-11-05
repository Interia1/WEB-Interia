(() => {
    let cartQueue = Promise.resolve();
    const noticeTimers = new WeakMap();

    const positionHeaderCart = () => {
        const header = document.querySelector('.cart-menu');
        const toggle = header?.querySelector('.cart-menu-toggle');
        const dropdown = header?.querySelector('.cart-menu-dropdown');
        if (!toggle || !dropdown) return;
        const width = dropdown.getBoundingClientRect().width;
        if (!width) return;
        const button = toggle.getBoundingClientRect();
        const parent = header.getBoundingClientRect();
        const center = button.left + button.width / 2;
        const left = Math.round(Math.max(8, Math.min(center - width / 2, window.innerWidth - width - 8)));
        dropdown.style.setProperty('--cart-menu-left', `${left - parent.left}px`);
        dropdown.style.setProperty('--cart-arrow-position', `${Math.max(7, Math.min(width - 7, center - left))}px`);
    };

    const showNotice = (picker, message, isError = false) => {
        const notice = picker.querySelector('[data-cart-notice]');
        if (!notice) return;
        clearTimeout(noticeTimers.get(notice));
        notice.textContent = message;
        notice.classList.toggle('is-error', isError);
        notice.setAttribute('role', isError ? 'alert' : 'status');
        notice.hidden = false;
        if (!isError) {
            noticeTimers.set(notice, setTimeout(() => { notice.hidden = true; }, 2500));
        }
    };

    const syncPicker = (picker, updated) => {
        const controls = [...picker.querySelectorAll('[data-cart-key]')];
        const updatedControls = new Map([...updated.querySelectorAll('[data-cart-key]')].map(control => [control.dataset.cartKey, control]));
        if (controls.length !== updatedControls.size || controls.some(control => !updatedControls.has(control.dataset.cartKey))) {
            throw new Error('Zoznam košíkov sa zmenil. Obnovte stránku.');
        }
        controls.forEach(control => {
            const fresh = updatedControls.get(control.dataset.cartKey);
            const badge = control.querySelector('.shop-cart-quantity');
            const freshBadge = fresh.querySelector('.shop-cart-quantity');
            badge.textContent = freshBadge.textContent;
            badge.setAttribute('aria-label', freshBadge.getAttribute('aria-label'));
            control.querySelectorAll('.shop-cart-adjust').forEach(button => {
                const freshButton = fresh.querySelector(`.shop-cart-adjust[value="${button.value}"]`);
                button.disabled = freshButton.disabled;
            });
        });
        const updateLabel = (current, fresh) => {
            if (current && fresh) current.setAttribute('aria-label', fresh.getAttribute('aria-label'));
        };
        updateLabel(picker.querySelector('.shop-cart-primary'), updated.querySelector('.shop-cart-primary'));
        const freshOptions = new Map([...updated.querySelectorAll('.shop-cart-option-add')].map(button => [button.value, button]));
        picker.querySelectorAll('.shop-cart-option-add').forEach(button => updateLabel(button, freshOptions.get(button.value)));
    };

    const syncHeader = fresh => {
        const current = document.querySelector('.cart-menu');
        if (!current) throw new Error('Zmena bola uložená, ale košík sa nepodarilo obnoviť. Obnovte stránku.');
        const toggle = current.querySelector('.cart-menu-toggle');
        const dropdown = current.querySelector('.cart-menu-dropdown');
        const freshToggle = fresh.querySelector('.cart-menu-toggle');
        const freshDropdown = fresh.querySelector('.cart-menu-dropdown');
        if (!toggle || !dropdown || !freshToggle || !freshDropdown) {
            throw new Error('Zmena bola uložená, ale košík sa nepodarilo obnoviť. Obnovte stránku.');
        }
        const scrolls = ['.cart-menu-carts', '.cart-menu-items'].map(selector => dropdown.querySelector(selector)?.scrollTop || 0);
        const hadFocus = dropdown.contains(document.activeElement);
        toggle.innerHTML = freshToggle.innerHTML;
        ['aria-label', 'title'].forEach(name => toggle.setAttribute(name, freshToggle.getAttribute(name)));
        dropdown.innerHTML = freshDropdown.innerHTML;
        ['.cart-menu-carts', '.cart-menu-items'].forEach((selector, index) => {
            const element = dropdown.querySelector(selector);
            if (element) element.scrollTop = scrolls[index];
        });
        if (hadFocus) toggle.focus();
        window.requestAnimationFrame(positionHeaderCart);
    };

    const refreshManagement = async () => {
        const current = document.querySelector('.cart-management-list');
        if (!current) return;
        const response = await fetch(window.location.href, { cache: 'no-store', headers: { Accept: 'text/html' } });
        if (!response.ok) throw new Error('Zmena bola uložená, ale správu košíkov sa nepodarilo obnoviť. Obnovte stránku.');
        const updated = new DOMParser().parseFromString(await response.text(), 'text/html').querySelector('.cart-management-list');
        if (!updated) throw new Error('Zmena bola uložená, ale správu košíkov sa nepodarilo obnoviť. Obnovte stránku.');
        current.querySelectorAll('.cart-rename-form input[name="name"]').forEach(input => {
            const replacement = updated.querySelector(`#${CSS.escape(input.id)}`);
            if (replacement) replacement.value = input.value;
        });
        current.replaceWith(updated);
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-cart-notice]:not([hidden])').forEach(notice => {
            noticeTimers.set(notice, setTimeout(() => { notice.hidden = true; }, 2500));
        });
        window.addEventListener('resize', positionHeaderCart);
    });

    document.addEventListener('shown.bs.dropdown', event => {
        if (event.target.matches('.cart-menu-toggle')) positionHeaderCart();
    });

    document.addEventListener('submit', event => {
        const form = event.target;
        if (form.matches('[data-header-cart-action]')) {
            event.preventDefault();
            const body = new FormData(form);
            if (form.dataset.headerCartAction === 'default') {
                document.querySelectorAll('.shop-product-row[id^="catalog-product-"]').forEach(row => {
                    body.append('product_ids[]', row.id.slice('catalog-product-'.length));
                });
            }
            cartQueue = cartQueue.then(async () => {
                try {
                    const response = await fetch(form.action, { method: 'POST', headers: { Accept: 'application/json' }, body });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Košík sa nepodarilo upraviť.'));
                    const header = new DOMParser().parseFromString(data.header, 'text/html').querySelector('.cart-menu');
                    const results = new DOMParser().parseFromString(data.results, 'text/html');
                    if (!header) throw new Error('Zmena bola uložená, ale košík sa nepodarilo obnoviť. Obnovte stránku.');
                    syncHeader(header);
                    if (form.dataset.headerCartAction === 'default') {
                        document.querySelectorAll('.shop-product-row[id^="catalog-product-"]').forEach(row => {
                            const picker = row.querySelector('[data-cart-picker]');
                            const freshPicker = results.getElementById(row.id)?.querySelector('[data-cart-picker]');
                            if (picker && !freshPicker) throw new Error('Zmena bola uložená, ale produkty sa nepodarilo obnoviť. Obnovte stránku.');
                            if (picker) picker.replaceWith(freshPicker);
                        });
                    } else {
                        const picker = document.getElementById(`catalog-product-${data.product_id}`)?.querySelector('[data-cart-picker]');
                        const freshPicker = results.querySelector('[data-cart-picker]');
                        if (picker && !freshPicker) throw new Error('Zmena bola uložená, ale produkt sa nepodarilo obnoviť. Obnovte stránku.');
                        if (picker) syncPicker(picker, freshPicker);
                    }
                    await refreshManagement();
                } catch (error) {
                    const notice = document.querySelector('[data-header-cart-error]');
                    if (notice) {
                        notice.textContent = error.message || 'Košík sa nepodarilo upraviť. Skúste to znova.';
                        notice.hidden = false;
                    } else {
                        const alert = document.createElement('div');
                        alert.className = 'container mt-3 alert alert-danger';
                        alert.setAttribute('role', 'alert');
                        alert.textContent = error.message || 'Košík sa nepodarilo upraviť. Skúste to znova.';
                        document.querySelector('main').prepend(alert);
                    }
                }
            });
            return;
        }
        if (!form.matches('.shop-product-cart-form')) return;
        const submitter = event.submitter || form.querySelector('.shop-cart-primary');
        if (!submitter) return;
        event.preventDefault();

        const row = form.closest('.shop-product-row');
        const body = new FormData(form);
        if (submitter.name) body.append(submitter.name, submitter.value);
        const action = submitter.hasAttribute('formaction') ? submitter.getAttribute('formaction') : form.action;

        cartQueue = cartQueue.then(async () => {
            try {
                const response = await fetch(action, {
                    method: 'POST',
                    headers: { Accept: 'application/json' },
                    body,
                });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Košík sa nepodarilo upraviť.'));
                }
                const parsed = new DOMParser().parseFromString(data.results, 'text/html');
                const freshPicker = parsed.querySelector('[data-cart-picker]');
                const header = new DOMParser().parseFromString(data.header, 'text/html').querySelector('.cart-menu');
                if (!freshPicker || !header) throw new Error('Zmena bola uložená, ale údaje sa nepodarilo obnoviť. Obnovte stránku.');
                const currentPicker = document.getElementById(`catalog-product-${data.product_id}`)?.querySelector('[data-cart-picker]');
                syncHeader(header);
                if (currentPicker) {
                    syncPicker(currentPicker, freshPicker);
                    showNotice(currentPicker, data.message);
                }
            } catch (error) {
                const message = error.message || 'Košík sa nepodarilo upraviť. Skúste to znova.';
                const currentPicker = document.getElementById(row.id)?.querySelector('[data-cart-picker]');
                if (currentPicker) {
                    showNotice(currentPicker, message, true);
                } else {
                    const alert = document.createElement('div');
                    alert.className = 'container mt-3 alert alert-danger';
                    alert.setAttribute('role', 'alert');
                    alert.textContent = message;
                    document.querySelector('main').prepend(alert);
                }
            }
        });
    });

    document.addEventListener('pointerdown', event => {
        if (event.pointerType !== 'mouse') return;
        const header = event.target.closest('.cart-menu');
        if (header) header.dataset.mouseInteraction = 'true';
        const details = event.target.closest('[data-cart-choices]');
        if (details) details.dataset.mouseInteraction = 'true';
    });

    document.addEventListener('pointerover', event => {
        if (event.pointerType !== 'mouse') return;
        const header = event.target.closest('.cart-menu');
        if (header && !header.contains(event.relatedTarget)) window.requestAnimationFrame(positionHeaderCart);
        const summary = event.target.closest('[data-cart-choices] > summary');
        if (!summary || summary.contains(event.relatedTarget)) return;
        const details = summary.parentElement;
        if (!details.open) {
            details.open = true;
            details.dataset.hoverOpened = 'true';
        }
    });

    document.addEventListener('pointerout', event => {
        if (event.pointerType !== 'mouse') return;
        const header = event.target.closest('.cart-menu');
        if (header && !header.contains(event.relatedTarget) && header.dataset.mouseInteraction) {
            const toggle = header.querySelector('.cart-menu-toggle');
            window.bootstrap?.Dropdown.getInstance(toggle)?.hide();
            delete header.dataset.mouseInteraction;
        }
        const details = event.target.closest('[data-cart-choices]');
        if (!details || details.contains(event.relatedTarget)) return;
        if (details.dataset.mouseInteraction || (details.dataset.hoverOpened && !details.contains(document.activeElement))) {
            details.open = false;
            delete details.dataset.hoverOpened;
            delete details.dataset.mouseInteraction;
        }
    });

    document.addEventListener('click', event => {
        const picker = event.target.closest('[data-cart-picker]');
        document.querySelectorAll('[data-cart-choices][open]').forEach(details => {
            if (!picker || !picker.contains(details)) {
                details.open = false;
                delete details.dataset.hoverOpened;
                delete details.dataset.mouseInteraction;
            }
        });
        const summary = event.target.closest('[data-cart-choices] > summary');
        if (!summary) return;
        const details = summary.parentElement;
        if (details.dataset.hoverOpened) {
            event.preventDefault();
            delete details.dataset.hoverOpened;
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        const details = event.target.closest('[data-cart-choices]');
        if (!details?.open) return;
        details.open = false;
        delete details.dataset.hoverOpened;
        delete details.dataset.mouseInteraction;
        details.querySelector('summary').focus();
    });
})();
