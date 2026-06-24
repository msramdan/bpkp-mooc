(function () {
    'use strict';

    function renderCoursesGrid(api) {
        var data = api.rows({ page: 'current' }).data().toArray();
        var grid = document.getElementById('courses-grid');
        var empty = document.getElementById('courses-grid-empty');

        if (!grid) {
            return;
        }

        if (!data.length) {
            grid.innerHTML = '';
            if (empty) {
                empty.classList.remove('d-none');
            }
            return;
        }

        if (empty) {
            empty.classList.add('d-none');
        }

        grid.innerHTML = data.map(function (row) {
            return row.card || '';
        }).join('');
    }

    function syncToolbar(api) {
        var info = document.getElementById('courses-info');
        var wrapper = document.getElementById('courses-table_wrapper');

        if (info && wrapper) {
            var infoNode = wrapper.querySelector('.dataTables_info');
            info.textContent = infoNode ? infoNode.textContent : '';
        }

        renderCoursesGrid(api);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var table = document.getElementById('courses-table');

        if (!table || typeof window.jQuery === 'undefined' || !window.jQuery.fn.DataTable) {
            return;
        }

        var $table = window.jQuery(table);

        var dt = $table.DataTable({
            processing: true,
            serverSide: true,
            ajax: table.dataset.ajaxUrl,
            pageLength: 12,
            lengthMenu: [[12, 24, 48], [12, 24, 48]],
            order: [[1, 'asc']],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
            columns: [
                { data: 'card', name: 'card', orderable: false, searchable: false },
                { data: 'judul', name: 'judul', visible: false },
                { data: 'kode', name: 'kode', visible: false },
                { data: 'kategori', name: 'kategori', visible: false },
                { data: 'level', name: 'level', visible: false },
                { data: 'modul_total', name: 'modul_total', visible: false, searchable: false },
                { data: 'enrollments_count', name: 'enrollments_count', visible: false, searchable: false },
                { data: 'is_published', name: 'is_published', visible: false, searchable: false },
            ],
            drawCallback: function () {
                syncToolbar(this.api());
            },
            initComplete: function () {
                var api = this.api();
                var lengthHost = document.getElementById('courses-length');
                var searchHost = document.getElementById('courses-search');
                var paginationHost = document.getElementById('courses-pagination');
                var wrapper = document.getElementById('courses-table_wrapper');

                if (wrapper && lengthHost) {
                    var length = wrapper.querySelector('.dataTables_length');
                    if (length) {
                        lengthHost.appendChild(length);
                    }
                }

                if (wrapper && searchHost) {
                    var filter = wrapper.querySelector('.dataTables_filter');
                    if (filter) {
                        searchHost.appendChild(filter);
                    }
                }

                if (wrapper && paginationHost) {
                    var paginate = wrapper.querySelector('.dataTables_paginate');
                    if (paginate) {
                        paginationHost.appendChild(paginate);
                    }
                }

                syncToolbar(api);
            },
        });

        window.adminCoursesDataTable = dt;
    });
})();
