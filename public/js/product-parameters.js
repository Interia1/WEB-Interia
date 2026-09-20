(() => {
    const container = document.querySelector('[data-product-parameters]');
    const category = document.getElementById('category');
    if (!container || !category) return;

    const update = () => {
        container.querySelectorAll('[data-parameter-category]').forEach(fieldset => {
            const active = fieldset.dataset.parameterCategory === category.value;
            fieldset.hidden = !active;
            fieldset.disabled = !active;
            if (active) {
                const label = document.getElementById('category_label');
                if (label) label.value = fieldset.dataset.categoryLabel;
            }
        });
    };
    category.addEventListener('input', update);
    category.addEventListener('change', update);
    document.querySelector('[data-select-products]')?.addEventListener('change', event => {
        document.querySelectorAll('input[name="product_ids[]"]').forEach(checkbox => {
            checkbox.checked = event.target.checked;
        });
    });
    update();
})();