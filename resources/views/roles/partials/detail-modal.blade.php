<div class="modal fade align-items-start" id="roleDetailModal" tabindex="-1" aria-labelledby="roleDetailModalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable mt-4">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleDetailModalTitle">{{ __('Role detail') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="text-center text-muted py-5" id="roleDetailModalLoading">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>{{ __('Loading…') }}
                </div>
                <div id="roleDetailModalError" class="alert alert-danger d-none mb-0"></div>
                <div id="roleDetailModalContent" class="d-none"></div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary btn-wave d-none" id="roleDetailModalEdit">
                    <i class="ri-pencil-line align-middle me-1"></i>{{ __('Edit') }}
                </a>
                <button type="button" class="btn btn-secondary-light btn-wave" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
