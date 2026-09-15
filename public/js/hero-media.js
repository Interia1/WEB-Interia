(() => {
    const heroMedia = document.querySelector('[data-hero-media]');

    if (!heroMedia) {
        return;
    }

    const hero = heroMedia.closest('.home-hero');
    const heroContent = hero.querySelector(':scope > .container');
    const customerInfo = document.querySelector('.home-customer-info');
    const sectionSubtitles = Array.from(customerInfo?.querySelectorAll('.home-info-subtitle') || []);
    const surroundingElements = [
        document.querySelector('.site-nav'),
        document.querySelector('.home-advertising'),
        document.querySelector('.cookie-banner'),
    ].filter(Boolean);
    const updateHeroHeight = () => {
        const surroundingHeight = surroundingElements.reduce((height, element) => {
            return height + element.getBoundingClientRect().height;
        }, 6);

        const visibleSubtitles = window.matchMedia('(min-width: 992px)').matches
            ? sectionSubtitles
            : sectionSubtitles.slice(0, 1);
        const sectionTop = customerInfo?.getBoundingClientRect().top || 0;
        const previewSpace = Math.max(0, ...visibleSubtitles.map(subtitle => {
            return subtitle.getBoundingClientRect().bottom - sectionTop;
        }));

        hero.style.setProperty('--home-surrounding-height', `${surroundingHeight + previewSpace}px`);
        if (heroContent) {
            hero.style.setProperty('--home-controls-height', `${heroContent.getBoundingClientRect().height + 20}px`);
        }
    };

    updateHeroHeight();

    if ('ResizeObserver' in window) {
        const layoutObserver = new ResizeObserver(updateHeroHeight);
        surroundingElements.forEach((element) => layoutObserver.observe(element));
        if (heroContent) {
            layoutObserver.observe(heroContent);
        }
        if (customerInfo) {
            layoutObserver.observe(customerInfo);
        }
        sectionSubtitles.forEach((subtitle) => layoutObserver.observe(subtitle));
    }

    window.addEventListener('resize', updateHeroHeight);

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