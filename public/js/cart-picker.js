(() => {
    document.addEventListener('pointerover', event => {
        if (event.pointerType !== 'mouse') return;
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
        const details = event.target.closest('[data-cart-choices]');
        if (!details || details.contains(event.relatedTarget)) return;
        if (details.dataset.hoverOpened && !details.contains(document.activeElement)) {
            details.open = false;
            delete details.dataset.hoverOpened;
        }
    });

    document.addEventListener('click', event => {
        const picker = event.target.closest('[data-cart-picker]');
        document.querySelectorAll('[data-cart-choices][open]').forEach(details => {
            if (!picker || !picker.contains(details)) {
                details.open = false;
                delete details.dataset.hoverOpened;
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
        details.querySelector('summary').focus();
    });
})();
