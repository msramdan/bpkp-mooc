@extends('layouts.app')

@section('title', __('Roles'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Roles') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Roles') }}</li>
            </ol>
        </div>
        @can('role & permission create')
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-wave">
                <i class="ri-add-line align-middle me-1"></i>{{ __('Create a new role') }}
            </a>
        @endcan
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="card-title mb-0">{{ __('Roles') }}</div>
                </div>
                <div class="card-body">
                    <x-alert />

                    <div class="table-responsive">
                        <table id="roles-table" class="table table-striped table-bordered text-nowrap w-100">
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

    @include('roles.partials.detail-modal')
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

            $('#roles-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: @json(route('roles.index')),
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

            var modalEl = document.getElementById('roleDetailModal');
            if (!modalEl) {
                return;
            }

            var ids = {
                loading: 'roleDetailModalLoading',
                error: 'roleDetailModalError',
                content: 'roleDetailModalContent',
                edit: 'roleDetailModalEdit',
            };

            function byId(id) {
                return document.getElementById(id);
            }

            function resetModalBody() {
                var loading = byId(ids.loading);
                var err = byId(ids.error);
                var content = byId(ids.content);
                var edit = byId(ids.edit);
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
                if (edit) {
                    edit.classList.add('d-none');
                    edit.href = '#';
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

            var roleModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            var msgLoadFail = @json(__('Could not load data.'));
            var msgNotFound = @json(__('Not found'));
            var lblName = @json(__('Name'));
            var lblPermissions = @json(__('Permissions'));
            var lblCreated = @json(__('Created at'));
            var lblUpdated = @json(__('Updated at'));

            modalEl.addEventListener('hidden.bs.modal', resetModalBody);

            var fetchController = null;
            $(document).on('click', '.js-open-role-detail', function(e) {
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
                roleModal.show();

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
                        var perms = Array.isArray(d.permissions) ? d.permissions : [];
                        var permHtml = perms.length ?
                            '<div class="d-flex flex-wrap gap-1">' + perms.map(function(p) {
                                return '<span class="badge bg-primary-transparent">' + M.escHtml(p) + '</span>';
                            }).join('') + '</div>' :
                            '<span class="text-muted">—</span>';
                        var html = '<table class="table table-sm table-borderless mb-0">' +
                            '<tr><th class="text-muted fw-normal" style="width:11rem">' + M.escHtml(lblName) +
                            '</th><td>' + M.escHtml(d.name) + '</td></tr>' +
                            '<tr><th class="text-muted fw-normal align-top">' + M.escHtml(lblPermissions) +
                            '</th><td>' + permHtml + '</td></tr>' +
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
                        if (d.edit_url) {
                            var ed = byId(ids.edit);
                            if (ed) {
                                ed.href = d.edit_url;
                                ed.classList.remove('d-none');
                            }
                        }
                    })
                    .catch(function(err) {
                        if (err.name === 'AbortError') {
                            return;
                        }
                        showLoadError(err && err.message ? err.message : msgLoadFail);
                    });
            });
        });
    </script>
@endpush
