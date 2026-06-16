(() => {
    const STORAGE_KEY = 'web_interia_cookie_consent_v1';

    const readConsent = () => {
        try {
            const value = localStorage.getItem(STORAGE_KEY);
            return value ? JSON.parse(value) : null;
        } catch {
            return null;
        }
    };

    const writeConsent = (consent) => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(consent));
        window.dispatchEvent(new CustomEvent('cookie:consent-updated', { detail: consent }));
    };

    const applyConsent = (consent) => {
        window.CookieConsent = {
            essential: true,
            analytics: !!consent.analytics,
            marketing: !!consent.marketing,
            has(category) {
                return category === 'essential' ? true : !!this[category];
            },
        };
    };

    document.addEventListener('DOMContentLoaded', () => {
        const banner = document.getElementById('cookieBanner');
        const modalEl = document.getElementById('cookieModal');
        const openModalButton = document.getElementById('openCookieModal');
        const saveButton = document.getElementById('saveCookieSettings');
        const analyticsInput = document.getElementById('analyticsCookies');
        const marketingInput = document.getElementById('marketingCookies');

        if (!banner || !modalEl || !openModalButton || !saveButton || !analyticsInput || !marketingInput) {
            return;
        }

        const modal = new bootstrap.Modal(modalEl);
        const saved = readConsent();

        if (saved) {
            applyConsent(saved);
        } else {
            banner.hidden = false;
            applyConsent({ essential: true, analytics: false, marketing: false });
        }

        const syncInputs = (consent) => {
            analyticsInput.checked = !!consent.analytics;
            marketingInput.checked = !!consent.marketing;
        };

        syncInputs(saved || { analytics: false, marketing: false });

        openModalButton.addEventListener('click', () => {
            const current = readConsent() || { analytics: false, marketing: false };
            syncInputs(current);
            modal.show();
        });

        document.querySelectorAll('[data-cookie-action]').forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.getAttribute('data-cookie-action');
                const consent = action === 'accept-all'
                    ? { essential: true, analytics: true, marketing: true }
                    : { essential: true, analytics: false, marketing: false };

                writeConsent(consent);
                applyConsent(consent);
                banner.hidden = true;
            });
        });

        saveButton.addEventListener('click', () => {
            const consent = {
                essential: true,
                analytics: analyticsInput.checked,
                marketing: marketingInput.checked,
            };

            writeConsent(consent);
            applyConsent(consent);
            banner.hidden = true;
            modal.hide();
        });
    });
})();
