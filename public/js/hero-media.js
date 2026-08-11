(() => {
    const heroMedia = document.querySelector('[data-hero-media]');

    if (!heroMedia) {
        return;
    }

    const items = Array.from(heroMedia.querySelectorAll('.home-hero-video-item'));

    if (items.length === 0) {
        return;
    }

    const cssTimeToMs = (value) => {
        const timeValue = value.trim();

        if (timeValue.endsWith('ms')) {
            return Number.parseFloat(timeValue) || 0;
        }

        if (timeValue.endsWith('s')) {
            return (Number.parseFloat(timeValue) || 0) * 1000;
        }

        return 0;
    };

    const intervalMs = Number(heroMedia.dataset.heroInterval) || 8000;
    const fadeDurationMs = cssTimeToMs(window.getComputedStyle(heroMedia).getPropertyValue('--hero-fade-duration'));
    const firstIntervalMs = Math.max(0, intervalMs - fadeDurationMs);
    const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    let activeIndex = Math.max(0, items.findIndex((item) => item.classList.contains('is-active')));
    let timerId = null;

    const setActiveItem = (nextIndex) => {
        activeIndex = nextIndex % items.length;

        items.forEach((item, index) => {
            item.classList.toggle('is-active', index === activeIndex);
        });
    };

    const stopRotation = () => {
        if (timerId !== null) {
            window.clearInterval(timerId);
            timerId = null;
        }
    };

    const startRotation = () => {
        stopRotation();
        setActiveItem(activeIndex);

        if (items.length < 2 || reducedMotionQuery.matches) {
            setActiveItem(0);
            return;
        }

        timerId = window.setTimeout(() => {
            setActiveItem(activeIndex + 1);

            timerId = window.setInterval(() => {
                setActiveItem(activeIndex + 1);
            }, intervalMs);
        }, firstIntervalMs);
    };

    reducedMotionQuery.addEventListener('change', startRotation);
    startRotation();
})();