(() => {
    const toggle = document.querySelector('[data-password-toggle]');

    if (!toggle) {
        return;
    }

    const passwordFields = document.querySelectorAll('[data-password-field]');

    toggle.addEventListener('change', () => {
        passwordFields.forEach((field) => {
            field.type = toggle.checked ? 'text' : 'password';
        });
    });
})();