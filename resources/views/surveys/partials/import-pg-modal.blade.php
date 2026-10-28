<div class="modal fade" id="importPgModal" tabindex="-1" aria-labelledby="importPgModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('surveys.import-pg.preview', $survey) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="importPgModalTitle">{{ __('Import Soal Pilihan Ganda') }}</h5>
                        <p class="text-muted mb-0 fs-12">{{ __('Tambahkan banyak soal sekaligus dari file Excel ke ":title".', ['title' => $survey->title]) }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <ol class="import-pg-steps list-unstyled mb-0">
                        <li class="import-pg-step">
                            <span class="import-pg-step__number">1</span>
                            <div class="import-pg-step__body">
                                <h6 class="mb-1">{{ __('Unduh template Excel') }}</h6>
                                <p class="text-muted mb-2">{{ __('Template sudah berisi kolom, contoh soal, dan petunjuk pengisian.') }}</p>
                                <a href="{{ route('surveys.import-pg.template') }}" class="btn btn-sm btn-success btn-wave">
                                    <i class="ri-download-2-line me-1"></i>{{ __('Unduh Template') }}
                                </a>
                            </div>
                        </li>
                        <li class="import-pg-step">
                            <span class="import-pg-step__number">2</span>
                            <div class="import-pg-step__body">
                                <h6 class="mb-1">{{ __('Isi soal di Excel') }}</h6>
                                <p class="text-muted mb-2">{{ __('Satu baris = satu soal. Isi minimal Pilihan A dan B, lalu pilih huruf kunci jawaban dari dropdown.') }}</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0 import-pg-example">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Pertanyaan') }}</th>
                                                <th>{{ __('Pilihan A') }}</th>
                                                <th>{{ __('Pilihan B') }}</th>
                                                <th>…</th>
                                                <th>{{ __('Kunci') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ __('Apa kepanjangan dari SPIP?') }}</td>
                                                <td>{{ __('Sistem Pengendalian Intern Pemerintah') }}</td>
                                                <td>{{ __('Sistem Pengawasan Internal Pusat') }}</td>
                                                <td>…</td>
                                                <td class="text-center fw-semibold text-success">A</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </li>
                        <li class="import-pg-step">
                            <span class="import-pg-step__number">3</span>
                            <div class="import-pg-step__body">
                                <h6 class="mb-1">{{ __('Unggah file yang sudah diisi') }}</h6>
                                <p class="text-muted mb-2">{{ __('Soal belum langsung disimpan. Anda akan melihat pratinjau dan bisa memperbaiki baris yang salah terlebih dahulu.') }}</p>
                                <label for="importPgFile" class="import-pg-dropzone">
                                    <i class="ri-upload-cloud-2-line import-pg-dropzone__icon"></i>
                                    <span class="import-pg-dropzone__text" data-empty-text="{{ __('Klik untuk memilih file Excel (.xlsx)') }}">{{ __('Klik untuk memilih file Excel (.xlsx)') }}</span>
                                    <span class="import-pg-dropzone__hint">{{ __('Maksimal 5 MB') }}</span>
                                </label>
                                <input type="file" name="import_file" id="importPgFile" class="d-none" accept=".xlsx,.xls" required>
                            </div>
                        </li>
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-primary btn-wave" id="importPgSubmit" disabled>
                        <i class="ri-eye-line me-1"></i>{{ __('Lihat Pratinjau') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('css')
    <style>
        .import-pg-step {
            position: relative;
            display: flex;
            gap: 14px;
            padding-bottom: 22px;
        }

        .import-pg-step:last-child {
            padding-bottom: 0;
        }

        .import-pg-step:not(:last-child)::before {
            position: absolute;
            top: 34px;
            bottom: 4px;
            left: 15px;
            width: 2px;
            content: '';
            background-color: var(--default-border, #e9ecef);
        }

        .import-pg-step__number {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: rgb(var(--primary-rgb, 33, 75, 154));
            color: #ffffff;
            font-weight: 600;
        }

        .import-pg-step__body {
            flex: 1;
            min-width: 0;
        }

        .import-pg-example {
            font-size: 12px;
        }

        .import-pg-dropzone {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 20px 16px;
            border: 2px dashed var(--default-border, #d1d5db);
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }

        .import-pg-dropzone:hover,
        .import-pg-dropzone.is-selected {
            border-color: rgb(var(--success-rgb, 38, 191, 148));
            background-color: rgba(var(--success-rgb, 38, 191, 148), 0.06);
        }

        .import-pg-dropzone__icon {
            font-size: 30px;
            color: rgb(var(--success-rgb, 38, 191, 148));
        }

        .import-pg-dropzone__text {
            font-weight: 600;
            word-break: break-all;
        }

        .import-pg-dropzone__hint {
            font-size: 12px;
            color: var(--text-muted, #6b7280);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('importPgFile');
            var submit = document.getElementById('importPgSubmit');
            var dropzone = document.querySelector('.import-pg-dropzone');
            var label = document.querySelector('.import-pg-dropzone__text');

            if (!input) {
                return;
            }

            input.addEventListener('change', function () {
                var file = input.files[0];
                label.textContent = file ? file.name : label.dataset.emptyText;
                dropzone.classList.toggle('is-selected', Boolean(file));
                submit.disabled = !file;
            });

            submit.closest('form').addEventListener('submit', function () {
                submit.disabled = true;
                submit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + @json(__('Membaca file...'));
            });
        });
    </script>
@endpush
