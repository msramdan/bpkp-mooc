(function () {
    const root = document.querySelector('[data-peserta-course-detail]');
    if (!root) {
        return;
    }

    const navButtons = root.querySelectorAll('[data-module-nav]');
    const panels = root.querySelectorAll('[data-module-panel]');
    const searchInput = root.querySelector('[data-module-search]');
    const emptyHint = root.querySelector('[data-module-empty]');

    function showModule(moduleId) {
        navButtons.forEach((btn) => {
            btn.classList.toggle('is-active', btn.dataset.moduleId === moduleId);
        });

        panels.forEach((panel) => {
            panel.classList.toggle('is-visible', panel.dataset.moduleId === moduleId);
        });

        const activeBtn = root.querySelector(`[data-module-nav][data-module-id="${moduleId}"]`);
        if (activeBtn) {
            activeBtn.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    navButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            showModule(btn.dataset.moduleId);
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            let visible = 0;

            navButtons.forEach((btn) => {
                const text = btn.dataset.searchText || '';
                const match = q === '' || text.includes(q);
                btn.classList.toggle('is-hidden', !match);
                if (match) {
                    visible += 1;
                }
            });

            if (emptyHint) {
                emptyHint.classList.toggle('d-none', visible > 0);
            }
        });
    }

    const initial = root.querySelector('[data-module-nav].is-active');
    if (initial) {
        showModule(initial.dataset.moduleId);
    }
})();
