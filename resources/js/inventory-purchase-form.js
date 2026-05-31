function initInventoryPurchaseForm(root = document) {
    const container = root.querySelector?.('#inv-purchase-lines') ?? document.getElementById('inv-purchase-lines');
    const addBtn = root.querySelector?.('#inv-add-line') ?? document.getElementById('inv-add-line');
    if (!container || !addBtn) {
        return;
    }

    addBtn.addEventListener('click', () => {
        const lines = container.querySelectorAll('.purchase-line');
        const index = lines.length;
        const template = lines[0];
        if (!template) {
            return;
        }

        const clone = template.cloneNode(true);
        clone.querySelectorAll('input, select').forEach((el) => {
            const name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', name.replace(/lines\[\d+]/, `lines[${index}]`));
            }
            if (el.tagName === 'SELECT') {
                el.selectedIndex = 0;
            } else {
                el.value = '';
            }
        });
        container.appendChild(clone);
    });
}

document.addEventListener('DOMContentLoaded', () => initInventoryPurchaseForm(document));
document.addEventListener('spa:navigated', (event) => {
    const root = event.detail?.root ?? document.getElementById('app-content') ?? document;
    initInventoryPurchaseForm(root);
});
