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
        var i18n = window.activityFormI18n || {};

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

        function setMode(mode) {
            var methodInput = document.getElementById('activityFormMethod');
            var fixedRequired = document.getElementById('activityFormIsRequiredFixed');
            var requiredHidden = document.getElementById('activityIsRequiredHidden');
            var flags = document.getElementById('activityFieldFlags');
            var modeLabel = document.getElementById('activityFormModeLabel');
            var submitBtn = document.getElementById('activityFormSubmitBtn');

            if (mode === 'edit') {
                if (methodInput) methodInput.disabled = false;
                if (fixedRequired) fixedRequired.disabled = true;
                if (requiredHidden) requiredHidden.disabled = false;
                if (flags) flags.classList.remove('d-none');
                if (modeLabel) modeLabel.textContent = i18n.edit || 'Ubah';
                if (submitBtn) submitBtn.textContent = i18n.save || 'Simpan';
            } else {
                if (methodInput) methodInput.disabled = true;
                if (fixedRequired) fixedRequired.disabled = false;
                if (requiredHidden) requiredHidden.disabled = true;
                if (flags) flags.classList.add('d-none');
                if (modeLabel) modeLabel.textContent = i18n.add || 'Tambah';
                if (submitBtn) submitBtn.textContent = i18n.save || 'Simpan';
            }
        }

        function mediaKindFromUrl(url) {
            var path = String(url || '').split('?')[0];
            var ext = (path.split('.').pop() || '').toLowerCase();
            if (['png', 'jpg', 'jpeg', 'webp', 'gif', 'bmp', 'svg'].indexOf(ext) !== -1) return { kind: 'image', ext: ext };
            if (ext === 'pdf') return { kind: 'pdf', ext: ext };
            if (['mp4', 'webm', 'mov', 'm4v', 'ogg', 'avi', 'mkv'].indexOf(ext) !== -1) return { kind: 'video', ext: ext };
            if (['txt', 'csv', 'log', 'md'].indexOf(ext) !== -1) return { kind: 'text', ext: ext };
            return { kind: 'other', ext: ext };
        }

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function renderCurrentMedia(url, title) {
            var wrap = document.getElementById('activityCurrentMediaWrap');
            var box = document.getElementById('activityCurrentMedia');
            if (!wrap || !box) {
                return;
            }
            if (!url) {
                wrap.classList.add('d-none');
                box.innerHTML = '';
                return;
            }

            var meta = mediaKindFromUrl(url);
            var fileName = decodeURIComponent(String(url).split('?')[0].split('/').pop() || title || 'file');
            box.innerHTML =
                '<div class="activity-file-preview activity-file-preview--compact">' +
                '  <div class="activity-file-preview__actions">' +
                '    <button type="button" class="btn btn-sm btn-primary-light btn-wave js-activity-file-preview"' +
                '      data-url="' + escapeHtml(url) + '"' +
                '      data-title="' + escapeHtml(title || fileName) + '"' +
                '      data-kind="' + escapeHtml(meta.kind) + '"' +
                '      data-ext="' + escapeHtml(meta.ext) + '"' +
                '      title="' + escapeHtml(i18n.preview || 'Preview') + '">' +
                '      <i class="ri-eye-line"></i><span>' + escapeHtml(i18n.preview || 'Preview') + '</span>' +
                '    </button>' +
                '    <a href="' + escapeHtml(url) + '" target="_blank" rel="noopener noreferrer"' +
                '      class="btn btn-sm btn-light btn-wave" title="' + escapeHtml(i18n.openTab || 'Tab baru') + '">' +
                '      <i class="ri-external-link-line"></i><span>' + escapeHtml(i18n.openTab || 'Tab baru') + '</span>' +
                '    </a>' +
                '  </div>' +
                '</div>';
            wrap.classList.remove('d-none');
        }

        function applyFieldVisibility(key, options) {
            options = options || {};
            var hasFile = !!options.hasFile;
            var hasVideo = !!options.hasVideo;

            var videoField = document.getElementById('activityFieldVideo');
            var urlField = document.getElementById('activityFieldUrl');
            var berkasField = document.getElementById('activityFieldBerkas');
            var penugasanRequirementsField = document.getElementById('activityFieldPenugasanRequirements');
            var surveyField = document.getElementById('activityFieldSurvey');
            var sertifikatField = document.getElementById('activityFieldSertifikat');
            var durasiField = document.getElementById('activityFieldDurasi');
            var namaCol = document.getElementById('activityFieldNamaCol');
            var videoInput = form.querySelector('[name="video_file"]');
            var urlInput = form.querySelector('[name="file_url"]');
            var berkasInput = form.querySelector('[name="berkas_file"]');
            var surveyInput = form.querySelector('[name="survey_id"]');

            videoField.classList.add('d-none');
            urlField.classList.add('d-none');
            berkasField.classList.add('d-none');
            if (penugasanRequirementsField) penugasanRequirementsField.classList.add('d-none');
            if (surveyField) surveyField.classList.add('d-none');
            if (sertifikatField) sertifikatField.classList.add('d-none');
            if (videoInput) {
                videoInput.required = false;
                videoInput.value = '';
            }
            if (urlInput) {
                urlInput.required = false;
            }
            if (berkasInput) {
                berkasInput.required = false;
                berkasInput.value = '';
            }
            if (surveyInput) {
                surveyInput.required = false;
            }

            if (key === 'survey') {
                if (durasiField) durasiField.classList.remove('d-none');
                if (namaCol) {
                    namaCol.classList.remove('col-md-12');
                    namaCol.classList.add('col-md-8');
                }
            } else {
                if (durasiField) durasiField.classList.add('d-none');
                if (namaCol) {
                    namaCol.classList.remove('col-md-8');
                    namaCol.classList.add('col-md-12');
                }
            }

            if (key === 'video') {
                videoField.classList.remove('d-none');
                if (videoInput) {
                    videoInput.required = !hasVideo;
                }
                var videoMark = videoField.querySelector('.activity-required-mark');
                if (videoMark) videoMark.classList.toggle('d-none', hasVideo);
            } else if (key === 'url') {
                urlField.classList.remove('d-none');
                if (urlInput) {
                    urlInput.required = true;
                }
            } else if (key === 'survey') {
                if (surveyField) surveyField.classList.remove('d-none');
                if (surveyInput) surveyInput.required = true;
            } else if (key === 'sertifikat') {
                if (sertifikatField) sertifikatField.classList.remove('d-none');
            } else if (key === 'berkas' || key === 'penugasan' || key === 'h5p') {
                berkasField.classList.remove('d-none');
                var berkasLabel = berkasField.querySelector('.form-label');
                var berkasHelp = berkasField.querySelector('.form-text');
                var berkasMark = berkasField.querySelector('.activity-required-mark');
                if (key === 'penugasan') {
                    if (berkasLabel) {
                        berkasLabel.innerHTML = (i18n.instructionFile || 'Berkas instruksi');
                    }
                    if (berkasHelp) {
                        berkasHelp.textContent = i18n.instructionHelp || 'Opsional. Word, PowerPoint, PDF, atau ZIP — materi/instruksi untuk peserta.';
                    }
                    if (berkasInput) {
                        berkasInput.setAttribute('accept', '.pdf,.doc,.docx,.ppt,.pptx,.zip');
                        berkasInput.setAttribute('data-max-kb', berkasField.getAttribute('data-penugasan-max-kb') || '10240');
                        berkasInput.setAttribute('data-label', i18n.instructionFile || 'Berkas instruksi');
                        berkasInput.required = false;
                    }
                    if (berkasMark) berkasMark.classList.add('d-none');
                    if (penugasanRequirementsField) penugasanRequirementsField.classList.remove('d-none');
                } else if (key === 'h5p') {
                    if (berkasLabel) {
                        berkasLabel.innerHTML = 'Upload Package (.h5p) <span class="text-danger activity-required-mark">*</span>';
                    }
                    if (berkasHelp) {
                        berkasHelp.textContent = berkasField.getAttribute('data-h5p-help') || 'Maksimal 200 MB. Format: .h5p';
                    }
                    if (berkasInput) {
                        berkasInput.setAttribute('accept', '.h5p');
                        berkasInput.setAttribute('data-max-kb', berkasField.getAttribute('data-h5p-max-kb') || '204800');
                        berkasInput.setAttribute('data-label', 'Berkas H5P');
                        berkasInput.required = !hasFile;
                    }
                    berkasMark = berkasField.querySelector('.activity-required-mark');
                    if (berkasMark) berkasMark.classList.toggle('d-none', hasFile);
                } else {
                    if (berkasLabel) {
                        berkasLabel.innerHTML = (i18n.uploadFile || 'Unggah berkas') + ' <span class="text-danger activity-required-mark">*</span>';
                    }
                    if (berkasHelp) {
                        berkasHelp.textContent = berkasField.getAttribute('data-berkas-help') || '';
                    }
                    if (berkasInput) {
                        berkasInput.setAttribute('accept', '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.txt,.png,.jpg,.jpeg,.webp');
                        berkasInput.setAttribute('data-max-kb', berkasField.getAttribute('data-berkas-max-kb') || '10240');
                        berkasInput.setAttribute('data-label', i18n.uploadFile || 'Berkas');
                        berkasInput.required = !hasFile;
                    }
                    berkasMark = berkasField.querySelector('.activity-required-mark');
                    if (berkasMark) berkasMark.classList.toggle('d-none', hasFile);
                }
            }
        }

        function openCreateForm(key, label) {
            setMode('create');
            form.reset();
            document.getElementById('activityFormType').value = key;
            document.getElementById('activityFormTypeLabel').textContent = label;
            form.action = storeUrl;
            renderCurrentMedia('', '');
            applyFieldVisibility(key, { hasFile: false, hasVideo: false });
            typeModal.hide();
            formModal.show();
        }

        function setCheckbox(nameOrId, checked) {
            var el = typeof nameOrId === 'string' && nameOrId.indexOf('#') === 0
                ? document.querySelector(nameOrId)
                : form.querySelector('[name="' + nameOrId + '"][type="checkbox"]');
            if (el) {
                el.checked = !!checked;
            }
        }

        function openEditForm(data) {
            if (!data || !data.update_url) {
                return;
            }

            setMode('edit');
            form.reset();

            var key = data.tipe || '';
            document.getElementById('activityFormType').value = key;
            document.getElementById('activityFormTypeLabel').textContent = data.type_label || key;
            form.action = data.update_url;

            var judul = form.querySelector('[name="judul"]');
            var body = form.querySelector('[name="body"]');
            var durasi = form.querySelector('[name="durasi_menit"]');
            if (judul) judul.value = data.judul || '';
            if (body) body.value = data.body || '';
            if (durasi) durasi.value = data.durasi_menit != null ? data.durasi_menit : 10;

            applyFieldVisibility(key, {
                hasFile: !!data.has_file,
                hasVideo: !!data.has_video,
            });

            var urlInput = form.querySelector('[name="file_url"]');
            if (urlInput && key === 'url') {
                urlInput.value = data.file_url || data.external_url || '';
            }

            var surveyInput = form.querySelector('[name="survey_id"]');
            if (surveyInput && data.survey_id) {
                surveyInput.value = String(data.survey_id);
            }

            var certTpl = document.getElementById('activityCertTemplate');
            var certSurvey = document.getElementById('activityCertSurvey');
            var passing = document.getElementById('activityPassingGrade');
            if (certTpl) certTpl.value = data.certificate_template_id ? String(data.certificate_template_id) : '';
            if (certSurvey) certSurvey.value = data.prerequisite_survey_id ? String(data.prerequisite_survey_id) : '';
            if (passing) passing.value = data.passing_grade != null ? data.passing_grade : 70;
            setCheckbox('require_all_lessons', data.require_all_lessons !== false);

            setCheckbox('#activityAssignText', !!data.assignment_allow_text);
            setCheckbox('#activityAssignFile', data.assignment_allow_file !== false);
            setCheckbox('#activityAssignLink', !!data.assignment_allow_link);

            setCheckbox('#activityIsPreview', !!data.is_preview);
            setCheckbox('#activityIsRequired', data.is_required !== false);

            var mediaUrl = data.media_url || data.external_url || '';
            if (key === 'url') {
                // URL type shows the link field; still show preview actions when possible
                renderCurrentMedia(data.external_url || data.file_url || '', data.judul || '');
            } else if (key === 'video') {
                renderCurrentMedia(mediaUrl || data.video_url || '', data.judul || '');
            } else if (key === 'berkas' || key === 'penugasan' || key === 'h5p') {
                renderCurrentMedia(mediaUrl || data.external_url || '', data.judul || '');
            } else {
                renderCurrentMedia('', '');
            }

            formModal.show();
        }

        document.querySelectorAll('#activityTypeGrid .activity-type-tile').forEach(function (tile) {
            tile.addEventListener('click', function () {
                if (tile.getAttribute('data-enabled') !== '1') {
                    return;
                }
                openCreateForm(
                    tile.getAttribute('data-type-key'),
                    tile.getAttribute('data-type-label')
                );
            });
        });

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.js-activity-edit');
            if (!btn) {
                return;
            }
            e.preventDefault();
            var raw = btn.getAttribute('data-activity') || '{}';
            try {
                openEditForm(JSON.parse(raw));
            } catch (err) {
                console.error('Invalid activity payload', err);
            }
        });

        formModalEl.addEventListener('hidden.bs.modal', function () {
            setMode('create');
            renderCurrentMedia('', '');
        });
    }

    function initActivityFilePreview() {
        var modalEl = document.getElementById('activityFilePreviewModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }

        var titleEl = document.getElementById('activityFilePreviewModalTitle');
        var bodyEl = document.getElementById('activityFilePreviewModalBody');
        var openTab = document.getElementById('activityFilePreviewOpenTab');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        var i18n = window.activityFilePreviewI18n || {};

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function renderBody(kind, url, ext) {
            if (kind === 'image') {
                return '<div class="activity-file-modal__frame is-image"><img src="' + escapeHtml(url) + '" alt=""></div>';
            }
            if (kind === 'pdf') {
                return '<iframe class="activity-file-modal__frame is-pdf" src="' + escapeHtml(url) + '#toolbar=1" title="PDF preview"></iframe>';
            }
            if (kind === 'video') {
                return '<div class="activity-file-modal__frame is-video"><video controls playsinline src="' + escapeHtml(url) + '"></video></div>';
            }
            if (kind === 'text') {
                return '<iframe class="activity-file-modal__frame is-text" src="' + escapeHtml(url) + '" title="Text preview"></iframe>';
            }

            var label = (i18n.unsupported || 'Preview tidak tersedia untuk tipe :ext. Gunakan tombol buka di tab baru.')
                .replace(':ext', (ext || '').toUpperCase() || 'FILE');

            return '' +
                '<div class="activity-file-modal__empty">' +
                '  <i class="ri-file-unknow-line" aria-hidden="true"></i>' +
                '  <p class="mb-0">' + escapeHtml(label) + '</p>' +
                '</div>';
        }

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.js-activity-file-preview');
            if (!btn) {
                return;
            }
            e.preventDefault();

            var url = btn.getAttribute('data-url') || '';
            var title = btn.getAttribute('data-title') || (i18n.preview || 'Preview');
            var kind = btn.getAttribute('data-kind') || 'other';
            var ext = btn.getAttribute('data-ext') || '';

            if (!url) {
                return;
            }

            if (titleEl) {
                titleEl.textContent = title;
            }
            if (openTab) {
                openTab.setAttribute('href', url);
            }
            if (bodyEl) {
                bodyEl.innerHTML = renderBody(kind, url, ext);
            }
            modal.show();
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            if (bodyEl) {
                bodyEl.innerHTML = '<div class="activity-file-modal__placeholder text-muted">' +
                    escapeHtml(i18n.loading || 'Memuat preview...') + '</div>';
            }
            if (openTab) {
                openTab.setAttribute('href', '#');
            }
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
        initActivityFilePreview();

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
