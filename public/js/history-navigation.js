document.addEventListener('DOMContentLoaded', () => {
    const backButton = document.querySelector('[data-history-back]');
    const forwardButton = document.querySelector('[data-history-forward]');
    const backCount = document.querySelector('[data-history-back-count]');
    const forwardCount = document.querySelector('[data-history-forward-count]');

    if (!backButton || !forwardButton || !backCount || !forwardCount) {
        return;
    }

    const storageKey = 'history-navigation-entries';
    const stateKey = '__historyNavigationEntry';
    let trackingAvailable = true;

    const fallbackCounts = () => {
        if (!trackingAvailable) return null;

        try {
            const stored = JSON.parse(window.sessionStorage.getItem(storageKey) || 'null');
            const entries = Array.isArray(stored?.entries) ? stored.entries : [];
            let current = entries.indexOf(window.history.state?.[stateKey]);

            if (current === -1) {
                const previous = window.history.length > 1 && Number.isInteger(stored?.current)
                    ? Math.min(stored.current, entries.length - 1)
                    : -1;
                entries.splice(previous + 1);
                const id = `${Date.now()}-${Math.random().toString(36).slice(2)}`;
                const state = window.history.state;
                window.history.replaceState({ ...(state && typeof state === 'object' ? state : {}), [stateKey]: id }, '');
                entries.push(id);
                current = entries.length - 1;
            }

            window.sessionStorage.setItem(storageKey, JSON.stringify({ entries, current }));
            return { back: current, forward: entries.length - current - 1 };
        } catch (error) {
            trackingAvailable = false;
            console.warn('Počet krokov histórie sa nepodarilo zistiť.', error);
            return null;
        }
    };

    const updateButtons = () => {
        let counts;
        if (window.navigation) {
            backButton.disabled = !window.navigation.canGoBack;
            forwardButton.disabled = !window.navigation.canGoForward;
            const entries = window.navigation.entries();
            const index = entries.findIndex(entry => entry === window.navigation.currentEntry);
            counts = index < 0 ? null : { back: index, forward: entries.length - index - 1 };
        } else {
            backButton.disabled = window.history.length <= 1;
            forwardButton.disabled = false;
            counts = fallbackCounts();
        }

        for (const [button, counter, label, count] of [
            [backButton, backCount, 'Späť', counts?.back],
            [forwardButton, forwardCount, 'Ďalej', counts?.forward],
        ]) {
            counter.textContent = count ?? '–';
            const description = count === undefined
                ? `${label} (počet krokov nie je dostupný)`
                : `${label} (známe kroky v rámci webu: ${count})`;
            button.setAttribute('aria-label', description);
            button.title = description;
        }
    };

    backButton.addEventListener('click', () => window.history.back());
    forwardButton.addEventListener('click', () => window.history.forward());
    window.addEventListener('pageshow', updateButtons);
    window.addEventListener('popstate', updateButtons);
    window.addEventListener('hashchange', updateButtons);
    window.addEventListener('catalog:historychange', updateButtons);

    if (window.navigation) {
        window.navigation.addEventListener('currententrychange', updateButtons);
        window.navigation.addEventListener('navigatesuccess', updateButtons);
    }

    updateButtons();
});