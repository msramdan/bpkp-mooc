<div class="modal fade" id="userCoursesModal" tabindex="-1" aria-labelledby="userCoursesModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="min-w-0">
                    <h5 class="modal-title mb-0" id="userCoursesModalTitle">{{ __('Kursus yang diikuti') }}</h5>
                    <p class="text-muted fs-13 mb-0 mt-1 text-truncate" id="userCoursesModalSubtitle"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body p-0">
                <div class="admin-participants-modal-profile">
                    <span class="admin-participants-modal-profile__avatar" id="userCoursesAvatar" aria-hidden="true">?</span>
                    <div class="min-w-0">
                        <div class="admin-participants-modal-profile__name" id="userCoursesName"></div>
                        <div class="admin-participants-modal-profile__email" id="userCoursesEmail"></div>
                    </div>
                </div>
                <div class="admin-participants-modal-loading text-center text-muted py-5" id="userCoursesLoading">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    {{ __('Loading…') }}
                </div>
                <div class="admin-participants-modal-empty d-none" id="userCoursesEmpty">
                    <i class="bi bi-journal-x"></i>
                    <p class="mb-0">{{ __('Belum mengikuti kursus.') }}</p>
                </div>
                <ul class="list-group list-group-flush admin-participants-modal-list d-none" id="userCoursesList"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
            </div>
        </div>
    </div>
</div>
