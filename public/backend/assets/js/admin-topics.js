(function () {
    'use strict';

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function initTopicSortable() {
        var list = document.getElementById('topics-sortable');
        if (!list || !list.dataset.reorderUrl || typeof Sortable === 'undefined') {
            return;
        }

        var topicCards = list.querySelectorAll('.topic-card');
        if (topicCards.length < 2) {
            return;
        }

        Sortable.create(list, {
            animation: 180,
            handle: '.topic-drag-handle',
            draggable: '.topic-card',
            ghostClass: 'topic-card--ghost',
            chosenClass: 'topic-card--chosen',
            dragClass: 'topic-card--drag',
            onEnd: function () {
                var cards = list.querySelectorAll('.topic-card[data-topic-id]');
                var order = Array.prototype.map.call(cards, function (card, index) {
                    var num = card.querySelector('.topic-order-num');
                    if (num) {
                        num.textContent = String(index + 1);
                    }
                    return card.getAttribute('data-topic-id');
                });

                fetch(list.dataset.reorderUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ order: order }),
                }).catch(function () {
                    window.location.reload();
                });
            },
        });
    }

    function initActivityModals() {
        var typeModalEl = document.getElementById('activityTypeModal');
        var formModalEl = document.getElementById('activityFormModal');
        if (!typeModalEl || !formModalEl || typeof bootstrap === 'undefined') {
            return;
        }

        var typeModal = bootstrap.Modal.getOrCreateInstance(typeModalEl);
        var formModal = bootstrap.Modal.getOrCreateInstance(formModalEl);
        var form = document.getElementById('activityCreateForm');
        var storeUrl = '';
        var searchInput = document.getElementById('activityTypeSearch');
        var grid = document.getElementById('activityTypeGrid');

        typeModalEl.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) {
                return;
            }
            storeUrl = button.getAttribute('data-topic-store') || '';
            if (searchInput) {
                searchInput.value = '';
            }
            filterTiles('');
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                filterTiles(searchInput.value.toLowerCase().trim());
            });
        }

        function filterTiles(query) {
            if (!grid) {
                return;
            }
            grid.querySelectorAll('.activity-type-tile').forEach(function (tile) {
                var hay = tile.getAttribute('data-search') || '';
                tile.classList.toggle('d-none', query !== '' && hay.indexOf(query) === -1);
            });
        }

        document.querySelectorAll('#activityTypeGrid .activity-type-tile').forEach(function (tile) {
            tile.addEventListener('click', function () {
                if (tile.getAttribute('data-enabled') !== '1') {
                    return;
                }

                var key = tile.getAttribute('data-type-key');
                var label = tile.getAttribute('data-type-label');

                document.getElementById('activityFormType').value = key;
                document.getElementById('activityFormTypeLabel').textContent = label;
                form.action = storeUrl;
                form.reset();
                document.getElementById('activityFormType').value = key;

                var videoField = document.getElementById('activityFieldVideo');
                var urlField = document.getElementById('activityFieldUrl');
                var berkasField = document.getElementById('activityFieldBerkas');
                var videoInput = form.querySelector('[name="video_file"]');
                var urlInput = form.querySelector('[name="file_url"]');
                var berkasInput = form.querySelector('[name="berkas_file"]');

                videoField.classList.add('d-none');
                urlField.classList.add('d-none');
                berkasField.classList.add('d-none');
                if (videoInput) {
                    videoInput.required = false;
                    videoInput.value = '';
                }
                if (urlInput) {
                    urlInput.required = false;
                    urlInput.value = '';
                }
                if (berkasInput) {
                    berkasInput.required = false;
                    berkasInput.value = '';
                }

                if (key === 'video') {
                    videoField.classList.remove('d-none');
                    if (videoInput) {
                        videoInput.required = true;
                    }
                } else if (key === 'url') {
                    urlField.classList.remove('d-none');
                    if (urlInput) {
                        urlInput.required = true;
                    }
                    var urlLabel = urlField.querySelector('.form-label');
                    var urlHelp = urlField.querySelector('.form-text');
                    if (urlLabel) urlLabel.innerHTML = 'Tautan URL <span class="text-danger">*</span>';
                    if (urlHelp) urlHelp.textContent = 'Masukkan tautan lengkap (http/https) yang akan dibuka peserta.';
                    if (urlInput) urlInput.placeholder = 'https://contoh.com/halaman';
                } else if (key === 'berkas' || key === 'penugasan' || key === 'h5p') {
                    berkasField.classList.remove('d-none');
                    if (berkasInput) {
                        berkasInput.required = true;
                    }
                    var berkasLabel = berkasField.querySelector('.form-label');
                    var berkasHelp = berkasField.querySelector('.form-text');
                    if (key === 'penugasan') {
                        if (berkasLabel) {
                            berkasLabel.innerHTML = 'Berkas instruksi <span class="text-danger">*</span>';
                        }
                        if (berkasHelp) {
                            berkasHelp.textContent = 'Word, PowerPoint, PDF, atau ZIP — materi/instruksi untuk peserta.';
                        }
                        if (berkasInput) {
                            berkasInput.setAttribute('accept', '.pdf,.doc,.docx,.ppt,.pptx,.zip');
                            berkasInput.setAttribute('data-max-kb', berkasField.getAttribute('data-penugasan-max-kb') || '10240');
                            berkasInput.setAttribute('data-label', 'Berkas instruksi');
                        }
                    } else if (key === 'h5p') {
                        if (berkasLabel) {
                            berkasLabel.innerHTML = 'Upload Package (.h5p) <span class="text-danger">*</span>';
                        }
                        if (berkasHelp) {
                            berkasHelp.textContent = berkasField.getAttribute('data-h5p-help') || 'Maksimal 200 MB. Format: .h5p';
                        }
                        if (berkasInput) {
                            berkasInput.setAttribute('accept', '.h5p');
                            berkasInput.setAttribute('data-max-kb', berkasField.getAttribute('data-h5p-max-kb') || '204800');
                            berkasInput.setAttribute('data-label', 'Berkas H5P');
                        }
                    } else {
                        if (berkasLabel) {
                            berkasLabel.innerHTML = 'Unggah berkas <span class="text-danger">*</span>';
                        }
                        if (berkasHelp) {
                            berkasHelp.textContent = berkasField.getAttribute('data-berkas-help') || '';
                        }
                        if (berkasInput) {
                            berkasInput.setAttribute('accept', '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.txt,.png,.jpg,.jpeg,.webp');
                            berkasInput.setAttribute('data-max-kb', berkasField.getAttribute('data-berkas-max-kb') || '10240');
                            berkasInput.setAttribute('data-label', 'Berkas');
                        }
                    }
                }

                typeModal.hide();
                formModal.show();
            });
        });
    }

    function initUploadSizeValidation() {
        document.querySelectorAll('.js-upload-size').forEach(function (input) {
            input.addEventListener('change', function () {
                var maxKb = parseInt(input.getAttribute('data-max-kb') || '0', 10);
                if (!maxKb || !input.files || !input.files.length) {
                    return;
                }

                var file = input.files[0];
                var maxBytes = maxKb * 1024;
                if (file.size <= maxBytes) {
                    return;
                }

                var label = input.getAttribute('data-label') || 'File';
                var maxMb = Math.round((maxKb / 1024) * 10) / 10;
                alert(label + ' maksimal ' + maxMb + ' MB.');
                input.value = '';
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initTopicSortable();
        initActivityModals();
        initUploadSizeValidation();

        // Tambahkan feedback visual saat mengunggah form yang memiliki file
        document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + (submitBtn.getAttribute('data-loading-text') || 'Mengunggah...');
                }
            });
        });
    });
})();
