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