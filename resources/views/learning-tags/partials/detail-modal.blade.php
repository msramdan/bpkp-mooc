<div class="modal fade align-items-start" id="learningTagDetailModal" tabindex="-1"
    aria-labelledby="learningTagDetailModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable mt-4">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="learningTagDetailModalTitle">{{ __('Learning tag detail') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="text-center text-muted py-5" id="learningTagDetailModalLoading">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>{{ __('Loading…') }}
                </div>
                <div id="learningTagDetailModalError" class="alert alert-danger d-none mb-0"></div>
                <div id="learningTagDetailModalContent" class="d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-light btn-wave"
                    data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
