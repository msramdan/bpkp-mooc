@extends('layouts.app')

@section('title', __('Learning Tags'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Learning Tags') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Learning Tags') }}</li>
            </ol>
        </div>
        @can('learning tag create')
            <button type="button" class="btn btn-primary btn-wave js-open-learning-tag-form" data-mode="create"
                data-store-url="{{ route('learning-tags.store') }}">
                <i class="ri-add-line align-middle me-1"></i>{{ __('Tambah') }}
            </button>
        @endcan
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="card-title mb-0">{{ __('Learning Tags') }}</div>
                </div>
                <div class="card-body">
                    <x-alert />

                    <div class="table-responsive">
                        <table id="learning-tags-table" class="table table-striped table-bordered text-nowrap w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th class="text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('learning-tags.partials.detail-modal')
    <div class="modal fade align-items-start" id="learningTagFormModal" tabindex="-1"
        aria-labelledby="learningTagFormModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable mt-4">
            <div class="modal-content">
                <form id="learningTagForm" method="POST" action="{{ route('learning-tags.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="learningTagFormMethod" value="POST">
                    <input type="hidden" name="_form_mode" id="learningTagFormMode" value="{{ old('_form_mode', 'create') }}">
                    <input type="hidden" name="_item_id" id="learningTagFormItemId" value="{{ old('_item_id') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="learningTagFormModalTitle">{{ __('Create Learning Tag') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-0">
                            <label class="form-label" for="learningTagName">{{ __('Name') }}</label>
                            <input type="text" name="name" id="learningTagName"
                                class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Name') }}"
                                value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-light btn-wave"
                            data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" id="learningTagFormSubmitBtn" class="btn btn-primary btn-wave">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    @include('partials.js.datatable-modal-detail-utils')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var M = window.BpkpDataTableModal;
            if (!M) {
                return;
            }

            $('#learning-tags-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: @json(route('learning-tags.index')),
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                order: [
                    [0, 'asc']
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                dom: "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6 text-md-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3'<'col-md-5'i><'col-md-7'p>>"
            });

            var modalEl = document.getElementById('learningTagDetailModal');
            if (!modalEl) {
                return;
            }

            var ids = {
                loading: 'learningTagDetailModalLoading',
                error: 'learningTagDetailModalError',
                content: 'learningTagDetailModalContent',
            };

            function byId(id) {
                return document.getElementById(id);
            }

            function resetModalBody() {
                var loading = byId(ids.loading);
                var err = byId(ids.error);
                var content = byId(ids.content);
                if (loading) {
                    loading.classList.remove('d-none');
                }
                if (err) {
                    err.classList.add('d-none');
                    err.textContent = '';
                }
                if (content) {
                    content.classList.add('d-none');
                    content.innerHTML = '';
                }
            }

            function showLoadError(message) {
                var loading = byId(ids.loading);
                var err = byId(ids.error);
                if (loading) {
                    loading.classList.add('d-none');
                }
                if (err) {
                    err.textContent = message;
                    err.classList.remove('d-none');
                }
            }

            var detailModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            var formModalEl = document.getElementById('learningTagFormModal');
            var formModal = formModalEl ? bootstrap.Modal.getOrCreateInstance(formModalEl) : null;
            var formEl = document.getElementById('learningTagForm');
            var formMethodEl = document.getElementById('learningTagFormMethod');
            var formModeEl = document.getElementById('learningTagFormMode');
            var formItemIdEl = document.getElementById('learningTagFormItemId');
            var formNameEl = document.getElementById('learningTagName');
            var formTitleEl = document.getElementById('learningTagFormModalTitle');
            var formSubmitEl = document.getElementById('learningTagFormSubmitBtn');
            var msgLoadFail = @json(__('Could not load data.'));
            var msgNotFound = @json(__('Not found'));
            var lblName = @json(__('Name'));
            var lblCreated = @json(__('Created at'));
            var lblUpdated = @json(__('Updated at'));
            var titleCreate = @json(__('Create Learning Tag'));
            var titleEdit = @json(__('Edit Learning Tag'));
            var txtSave = @json(__('Save'));
            var txtUpdate = @json(__('Update'));
            var storeUrl = @json(route('learning-tags.store'));

            modalEl.addEventListener('hidden.bs.modal', resetModalBody);

            var fetchController = null;
            $(document).on('click', '.js-open-learning-tag-detail', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                if (!url) {
                    return;
                }
                if (fetchController) {
                    fetchController.abort();
                }
                fetchController = new AbortController();
                resetModalBody();
                detailModal.show();

                M.jsonDetailFetch(url, fetchController.signal)
                    .then(function(r) {
                        if (r.status === 404) {
                            throw new Error(msgNotFound);
                        }
                        if (!r.ok) {
                            throw new Error(msgLoadFail);
                        }
                        return r.json();
                    })
                    .then(function(d) {
                        if (fetchController.signal.aborted) {
                            return;
                        }
                        var loadingEl = byId(ids.loading);
                        if (loadingEl) {
                            loadingEl.classList.add('d-none');
                        }
                        var html = '<table class="table table-sm table-borderless mb-0">' +
                            '<tr><th class="text-muted fw-normal" style="width:11rem">' + M.escHtml(lblName) +
                            '</th><td>' + M.escHtml(d.name) + '</td></tr>' +
                            '<tr><th class="text-muted fw-normal">' + M.escHtml(lblCreated) + '</th><td>' + M.escHtml(
                                d.created_at) + '</td></tr>' +
                            '<tr><th class="text-muted fw-normal">' + M.escHtml(lblUpdated) + '</th><td>' + M.escHtml(
                                d.updated_at) + '</td></tr>' +
                            '</table>';
                        var wrap = byId(ids.content);
                        if (wrap) {
                            wrap.innerHTML = html;
                            wrap.classList.remove('d-none');
                        }
                    })
                    .catch(function(err) {
                        if (err.name === 'AbortError') {
                            return;
                        }
                        showLoadError(err && err.message ? err.message : msgLoadFail);
                    });
            });

            function openFormModal(mode, options) {
                if (!formModal || !formEl || !formMethodEl || !formModeEl || !formItemIdEl || !formNameEl || !formTitleEl || !formSubmitEl) {
                    return;
                }
                var opts = options || {};
                if (mode === 'edit') {
                    formEl.action = opts.updateUrl || storeUrl;
                    formMethodEl.value = 'PUT';
                    formModeEl.value = 'edit';
                    formItemIdEl.value = opts.id || '';
                    formNameEl.value = opts.name || '';
                    formTitleEl.textContent = titleEdit;
                    formSubmitEl.textContent = txtUpdate;
                } else {
                    formEl.action = storeUrl;
                    formMethodEl.value = 'POST';
                    formModeEl.value = 'create';
                    formItemIdEl.value = '';
                    formNameEl.value = '';
                    formTitleEl.textContent = titleCreate;
                    formSubmitEl.textContent = txtSave;
                }
                formModal.show();
            }

            $(document).on('click', '.js-open-learning-tag-form', function() {
                var mode = ($(this).data('mode') || 'create').toString();
                openFormModal(mode, {
                    updateUrl: $(this).data('update-url') || '',
                    name: $(this).data('name') || '',
                    id: $(this).data('id') || ''
                });
            });

            @if ($errors->has('name') && in_array(old('_form_mode'), ['create', 'edit'], true))
                openFormModal(@json(old('_form_mode')), {
                    updateUrl: @json(old('_form_mode') === 'edit' && old('_item_id') ? route('learning-tags.update', old('_item_id')) : ''),
                    name: @json(old('name', '')),
                    id: @json(old('_item_id', ''))
                });
            @endif
        });
    </script>
@endpush
