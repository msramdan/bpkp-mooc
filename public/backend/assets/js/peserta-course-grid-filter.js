/**
 * Filter + search for peserta kursus / katalog grids.
 */
(function () {
    function initGridFilter(wrap) {
        const gridSelector = wrap.dataset.grid;
        const grid = document.querySelector(gridSelector);
        if (!grid) {
            return;
        }

        const itemSelector = wrap.dataset.itemSelector || '.peserta-kursus-item, .peserta-katalog-item';
        const filterKey = wrap.dataset.filterKey || 'status';
        const searchInput = wrap.querySelector('[data-search-input]');
        const clearBtn = wrap.querySelector('[data-search-clear]');
        const filterBtns = wrap.querySelectorAll('[data-filter-btn]');
        const summaryEl = wrap.querySelector('[data-result-summary]');
        const noResultsEl = document.querySelector(wrap.dataset.noResults || '');
        const total = parseInt(wrap.dataset.total || '0', 10);
        const msgVisible = wrap.dataset.msgVisible || 'Menampilkan :visible dari :total';
        const msgNone = wrap.dataset.msgNone || 'Tidak ada kursus yang cocok.';

        let activeFilter = 'all';

        function getItems() {
            return grid.querySelectorAll(itemSelector);
        }

        function updateSummary(visible) {
            if (!summaryEl) {
                return;
            }
            const template = summaryEl.dataset.labelTemplate || '';
            if (visible === total && template !== '') {
                summaryEl.textContent = template;
            } else {
                summaryEl.textContent = msgVisible
                    .replace(':visible', String(visible))
                    .replace(':total', String(total));
            }
        }

        function applyFilters() {
            const query = (searchInput?.value || '').trim().toLowerCase();
            const items = getItems();
            let visible = 0;

            items.forEach(function (el) {
                const filterVal = el.dataset[filterKey] || '';
                const matchFilter = activeFilter === 'all' || filterVal === activeFilter;
                const haystack = (el.dataset.search || '').toLowerCase();
                const matchSearch = query === '' || haystack.indexOf(query) !== -1;
                const show = matchFilter && matchSearch;

                el.style.display = show ? '' : 'none';
                if (show) {
                    visible++;
                }
            });

            updateSummary(visible);

            if (noResultsEl) {
                const showEmpty = items.length > 0 && visible === 0;
                noResultsEl.classList.toggle('d-none', !showEmpty);
                grid.classList.toggle('d-none', showEmpty);
            }

            if (clearBtn) {
                clearBtn.classList.toggle('d-none', query === '');
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        if (clearBtn && searchInput) {
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                searchInput.focus();
                applyFilters();
            });
        }

        filterBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                filterBtns.forEach(function (b) {
                    b.classList.remove('active');
                });
                btn.classList.add('active');
                activeFilter = btn.dataset.filter || 'all';
                applyFilters();
            });
        });

        applyFilters();
    }

    document.querySelectorAll('[data-peserta-grid-filter]').forEach(initGridFilter);
})();
