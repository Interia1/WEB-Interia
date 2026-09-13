document.addEventListener('DOMContentLoaded', () => {
    const backButton = document.querySelector('[data-history-back]');
    const forwardButton = document.querySelector('[data-history-forward]');

    if (!backButton || !forwardButton) {
        return;
    }

    const updateButtons = () => {
        if (window.navigation) {
            backButton.disabled = !window.navigation.canGoBack;
            forwardButton.disabled = !window.navigation.canGoForward;
            return;
        }

        backButton.disabled = window.history.length <= 1;
        forwardButton.disabled = false;
    };

    backButton.addEventListener('click', () => window.history.back());
    forwardButton.addEventListener('click', () => window.history.forward());
    window.addEventListener('pageshow', updateButtons);

    if (window.navigation) {
        window.navigation.addEventListener('navigatesuccess', updateButtons);
    }

    updateButtons();
});