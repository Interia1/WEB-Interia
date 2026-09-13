document.addEventListener('DOMContentLoaded', () => {
    const siteNotice = document.querySelector('.site-notice');

    if (!siteNotice) {
        return;
    }

    window.setTimeout(() => {
        siteNotice.classList.add('is-hiding');
        window.setTimeout(() => siteNotice.remove(), 650);
    }, 2000);
});