@extends('layouts.app')

@section('title', __('Survey Builder: ') . $survey->title)

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Pembangun Kuesioner') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('surveys.index') }}">{{ __('Bank Soal / Kuesioner') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $survey->title }}</li>
            </ol>
        </div>
        <div>
            <a href="{{ route('surveys.index') }}" class="btn btn-light btn-wave me-2">
                <i class="ri-arrow-left-line align-middle me-1"></i>{{ __('Kembali') }}
            </a>
            <button class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#questionModal" onclick="openCreateModal()">
                <i class="ri-add-line align-middle me-1"></i>{{ __('Tambah Pertanyaan') }}
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-8">
            <div class="card custom-card">
                <div class="card-header border-bottom">
                    <div class="card-title">{{ $survey->title }}</div>
                    <div class="text-muted ms-auto" style="font-size: 0.85rem;">{{ $survey->questions->count() }} Pertanyaan</div>
                </div>
                <div class="card-body p-0">
                    @if($survey->questions->isEmpty())
                        <div class="p-5 text-center text-muted">
                            <i class="ri-survey-line fs-1 mb-3 d-block text-gray-300"></i>
                            <p>{{ __('Belum ada pertanyaan. Silakan tambahkan pertanyaan pertama Anda.') }}</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush" id="question-list">
                            @foreach($survey->questions as $index => $question)
                                <li class="list-group-item p-4" data-id="{{ $question->id }}">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ri-drag-move-2-line text-muted cursor-move handle" style="cursor: grab;" title="Geser untuk mengurutkan"></i>
                                            <h6 class="mb-0 fw-semibold">
                                                {{ $index + 1 }}. {{ $question->question_text }}
                                                @if($question->is_required)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </h6>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-2-fill"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);" onclick="openEditModal({{ json_encode($question) }}, {{ json_encode($question->options) }})">
                                                        <i class="ri-edit-line me-2 align-middle text-info"></i>{{ __('Edit') }}
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('surveys.questions.destroy', [$survey, $question]) }}" method="POST">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger js-delete-confirm"
                                                            data-swal-title="{{ __('Hapus Pertanyaan Ini?') }}" 
                                                            data-swal-text="{{ __('Pertanyaan beserta seluruh opsi jawabannya akan dihapus permanen.') }}"
                                                            data-swal-confirm="{{ __('Ya, Hapus') }}" 
                                                            data-swal-cancel="{{ __('Batal') }}">
                                                            <i class="ri-delete-bin-line me-2 align-middle"></i>{{ __('Hapus') }}
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="ms-4 ps-2">
                                        @if($question->type === 'text')
                                            <textarea class="form-control" rows="2" placeholder="Jawaban teks panjang akan ditulis di sini..." disabled></textarea>
                                        @elseif($question->type === 'rating')
                                            <div class="d-flex gap-3">
                                                @for($i=1; $i<=5; $i++)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" disabled>
                                                        <label class="form-check-label">{{ $i }}</label>
                                                    </div>
                                                @endfor
                                            </div>
                                        @elseif($question->type === 'radio' || $question->type === 'checkbox')
                                            @foreach($question->options as $option)
                                                <div class="form-check mb-2 d-flex align-items-center flex-wrap gap-2">
                                                    <input class="form-check-input mt-0" type="{{ $question->type }}" disabled>
                                                    <label class="form-check-label mb-0">{{ $option->option_text }}</label>
                                                    @if($option->is_correct)
                                                        <span class="badge bg-success-transparent ms-1" title="Kunci Jawaban"><i class="ri-check-line me-1"></i>Kunci</span>
                                                    @endif
                                                    @if($option->score_value > 0)
                                                        <span class="badge bg-info-transparent">{{ $option->score_value }} Poin</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="ms-4 ps-2 mt-2">
                                        <span class="badge bg-light text-dark border"><i class="ri-price-tag-3-line me-1"></i>{{ strtoupper($question->type) }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">{{ __('Petunjuk') }}</div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <div class="d-flex align-items-center gap-2 mb-1 fw-medium"><i class="ri-text-spacing text-primary"></i> Teks Bebas (Text)</div>
                            <span class="text-muted fs-13">Gunakan untuk pertanyaan esai atau masukan yang membutuhkan jawaban panjang.</span>
                        </li>
                        <li class="mb-3">
                            <div class="d-flex align-items-center gap-2 mb-1 fw-medium"><i class="ri-list-radio text-primary"></i> Pilihan Ganda (Radio)</div>
                            <span class="text-muted fs-13">Peserta hanya bisa memilih SATU jawaban.</span>
                        </li>
                        <li class="mb-3">
                            <div class="d-flex align-items-center gap-2 mb-1 fw-medium"><i class="ri-checkbox-multiple-line text-primary"></i> Kotak Centang (Checkbox)</div>
                            <span class="text-muted fs-13">Peserta bisa memilih LEBIH DARI SATU jawaban.</span>
                        </li>
                        <li class="mb-0">
                            <div class="d-flex align-items-center gap-2 mb-1 fw-medium"><i class="ri-star-line text-primary"></i> Bintang (Rating)</div>
                            <span class="text-muted fs-13">Otomatis menyediakan skala nilai 1 sampai 5.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Pertanyaan -->
    <div class="modal fade" id="questionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="questionForm" method="POST" action="{{ route('surveys.questions.store', $survey) }}" novalidate>
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">{{ __('Tambah Pertanyaan') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row gy-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Tipe Pertanyaan') }} <span class="text-danger">*</span></label>
                                <select class="form-select" name="type" id="questionType" required>
                                    <option value="radio">{{ __('Pilihan Ganda (Radio)') }}</option>
                                    <option value="checkbox">{{ __('Kotak Centang (Checkbox)') }}</option>
                                    <option value="text">{{ __('Isian Teks (Esai)') }}</option>
                                    <option value="rating">{{ __('Skala Rating (1-5)') }}</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <div class="form-check form-switch mt-md-4 pt-md-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="isRequired" name="is_required" value="1" checked>
                                    <label class="form-check-label" for="isRequired">{{ __('Wajib Dijawab') }}</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('Teks Pertanyaan') }} <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="question_text" id="questionText" rows="3" required></textarea>
                            </div>
                            
                            <div class="col-12" id="ratingInfo" style="display:none;">
                                <div class="alert alert-info d-flex mb-0" role="alert">
                                    <i class="ri-star-fill fs-20 me-2 text-primary mt-1"></i>
                                    <div>
                                        <strong>Penilaian Otomatis Skala Rating (1 – 5 Poin):</strong><br>
                                        Sistem menerapkan standar evaluasi di mana semakin besar angka bernilai semakin tinggi:<br>
                                        • <strong>1 Poin</strong> = Nilai Terendah (Sangat Buruk / Kurang / Sangat Tidak Setuju)<br>
                                        • <strong>5 Poin</strong> = Nilai Tertinggi (Sangat Baik / Sempurna / Sangat Setuju)<br>
                                        <span class="fs-12 text-muted"><em>Di halaman kuesioner peserta, panduan skala dari 1 (Terendah) hingga 5 (Tertinggi) telah dilengkapi indikator warna dan lambang bintang.</em></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12" id="textInfo" style="display:none;">
                                <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
                                    <i class="ri-time-line fs-18 me-2"></i>
                                    <div><strong>Penilaian Manual:</strong> Jawaban Esai akan berstatus <em>Pending</em> saat dikumpulkan. Instruktur/Admin memberi nilai secara manual (0–100 Poin) melalui Dasbor Rekap.</div>
                                </div>
                            </div>
                            <!-- Area Opsi Jawaban (Hanya untuk Radio & Checkbox) -->
                            <div class="col-12" id="optionsArea">
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label mb-0 fw-semibold">{{ __('Pilihan Jawaban & Penentuan Kunci') }}</label>
                                    <button type="button" class="btn btn-sm btn-light btn-wave" onclick="addOptionRow()">
                                        <i class="ri-add-line"></i> {{ __('Tambah Opsi') }}
                                    </button>
                                </div>
                                <div class="fs-12 text-muted mb-3 d-flex align-items-center">
                                    <i class="ri-information-fill text-primary me-1 fs-15"></i>
                                    <span id="keyHintText">{{ __('Untuk Pilihan Ganda (Radio), Anda hanya dapat menandai 1 Kunci Jawaban. Soal bernilai 100 jika benar, 0 jika salah.') }}</span>
                                </div>
                                <div id="optionsContainer">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                        <button type="submit" class="btn btn-primary btn-wave">{{ __('Simpan Pertanyaan') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Logika Visibilitas Opsi
            const typeSelect = document.getElementById('questionType');
            const optionsArea = document.getElementById('optionsArea');
            const keyHintText = document.getElementById('keyHintText');
            
            function updateOptionInputsType() {
                const type = typeSelect.value;
                const inputs = document.querySelectorAll('.option-correct-checkbox');
                let hasChecked = false;

                inputs.forEach((input) => {
                    if (type === 'radio') {
                        input.type = 'radio';
                        input.name = 'option_correct_radio_ui';
                        // Jika pindah dari checkbox yang tadinya dicentang > 1, sisakan HANYA yang pertama!
                        if (input.checked) {
                            if (hasChecked) {
                                input.checked = false;
                                const hidden = input.parentElement.querySelector('.option-correct-hidden');
                                if (hidden) hidden.value = '0';
                            } else {
                                hasChecked = true;
                            }
                        }
                    } else {
                        input.type = 'checkbox';
                        input.removeAttribute('name');
                    }
                });
            }

            function toggleOptions() {
                const type = typeSelect.value;
                const ratingInfo = document.getElementById('ratingInfo');
                const textInfo = document.getElementById('textInfo');
                if (ratingInfo) ratingInfo.style.display = type === 'rating' ? 'block' : 'none';
                if (textInfo) textInfo.style.display = type === 'text' ? 'block' : 'none';

                if (['radio', 'checkbox'].includes(type)) {
                    optionsArea.style.display = 'block';
                    const container = document.getElementById('optionsContainer');
                    const currentCount = container ? container.querySelectorAll('.option-row').length : 0;
                    if (currentCount < 2) {
                        for (let i = currentCount; i < 2; i++) {
                            addOptionRow('', 0, false);
                        }
                    }
                    document.querySelectorAll('input[name="options[]"]').forEach(el => el.required = true);
                    if (keyHintText) {
                        if (type === 'radio') {
                            keyHintText.textContent = 'Untuk Pilihan Ganda (Radio), hanya boleh 1 Kunci Jawaban. Soal bernilai 100 jika benar, dan 0 jika salah.';
                        } else {
                            keyHintText.textContent = 'Untuk Kotak Centang (Checkbox), Anda bisa memilih lebih dari 1 Kunci. Aturan All-or-Nothing: Peserta harus mencentang SEMUA kunci dengan tepat untuk mendapat 100 poin (jika kurang atau salah 1 saja bernilai 0).';
                        }
                    }
                    updateOptionInputsType();
                } else {
                    optionsArea.style.display = 'none';
                    document.querySelectorAll('input[name="options[]"]').forEach(el => el.required = false);
                }
            }
            
            typeSelect.addEventListener('change', toggleOptions);
            toggleOptions();

            // Hapus bingkai merah (is-invalid) saat user mulai mengetik
            document.addEventListener('input', function(e) {
                if (e.target && (e.target.id === 'questionText' || e.target.name === 'options[]')) {
                    if (e.target.value.trim()) {
                        e.target.classList.remove('is-invalid');
                    }
                }
            });

            const questionForm = document.getElementById('questionForm');
            if (questionForm) {
                questionForm.addEventListener('submit', function (e) {
                    const type = typeSelect.value;
                    
                    // Kustomisasi Validasi Wajib Isi
                    let isFormValid = true;
                    let firstEmptyInput = null;
                    questionForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                    const questionText = document.getElementById('questionText');
                    if (!questionText.value.trim()) {
                        isFormValid = false;
                        questionText.classList.add('is-invalid');
                        if (!firstEmptyInput) firstEmptyInput = questionText;
                    }

                    if (['radio', 'checkbox'].includes(type)) {
                        const optionInputs = document.querySelectorAll('input[name="options[]"]');
                        optionInputs.forEach(input => {
                            if (!input.value.trim()) {
                                isFormValid = false;
                                input.classList.add('is-invalid');
                                if (!firstEmptyInput) firstEmptyInput = input;
                            }
                        });
                    }

                    if (!isFormValid) {
                        e.preventDefault();
                        if (firstEmptyInput) firstEmptyInput.focus();
                        if (typeof BpkpSwal !== 'undefined') {
                            BpkpSwal.alert('Harap isi Teks Pertanyaan dan seluruh teks Opsi Jawaban sebelum menyimpan!', 'Data Belum Lengkap', 'warning');
                        } else {
                            alert('Harap isi Teks Pertanyaan dan seluruh teks Opsi Jawaban sebelum menyimpan!');
                        }
                        return false;
                    }

                    // Logic Validasi Opsi Minimal
                    if (['radio', 'checkbox'].includes(type)) {
                        const optionRows = document.querySelectorAll('.option-row');
                        if (optionRows.length < 2) {
                            e.preventDefault();
                            if (typeof BpkpSwal !== 'undefined') {
                                BpkpSwal.alert('Pertanyaan bertipe Pilihan Ganda dan Kotak Centang wajib memiliki minimal 2 opsi jawaban!', 'Gagal Menyimpan', 'error');
                            } else {
                                alert('Pertanyaan bertipe Pilihan Ganda dan Kotak Centang wajib memiliki minimal 2 opsi jawaban!');
                            }
                            return false;
                        }
                    }

                    if (type === 'radio') {
                        const checkedKeys = document.querySelectorAll('.option-correct-checkbox:checked').length;
                        if (checkedKeys !== 1) {
                            e.preventDefault();
                            if (typeof BpkpSwal !== 'undefined') {
                                BpkpSwal.alert('Tipe Pilihan Ganda (Radio) mewajibkan tepat 1 Kunci Jawaban yang benar! Silakan pilih salah satu opsi sebagai Kunci Jawaban sebelum menyimpan.', 'Kunci Jawaban Belum Dipilih', 'warning');
                            } else {
                                alert('Tipe Pilihan Ganda (Radio) mewajibkan tepat 1 Kunci Jawaban yang benar!');
                            }
                            return false;
                        }
                    } else if (type === 'checkbox') {
                        const checkedKeys = document.querySelectorAll('.option-correct-checkbox:checked').length;
                        if (checkedKeys < 2) {
                            e.preventDefault();
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Kunci Jawaban Checkbox Kurang',
                                    text: 'Syarat memakai tipe Kotak Centang (Checkbox) adalah memiliki minimal 2 Kunci Jawaban yang benar. Jika Anda memang memilih hanya 1 jawaban benar, silakan ubah menjadi Pilihan Ganda.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: '<i class="ri-arrow-left-right-line me-1"></i> Ubah ke Pilihan Ganda (Radio)',
                                    cancelButtonText: 'Batal & Tandai Kunci Lagi',
                                    customClass: {
                                        confirmButton: 'btn btn-primary btn-wave me-2 px-3',
                                        cancelButton: 'btn btn-light btn-wave px-3'
                                    },
                                    buttonsStyling: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        typeSelect.value = 'radio';
                                        typeSelect.dispatchEvent(new Event('change'));
                                    }
                                });
                            } else {
                                alert('Tipe Kotak Centang (Checkbox) minimal harus memiliki 2 Kunci Jawaban yang benar!');
                            }
                            return false;
                        }
                    }
                });
            }

            // Drag and Drop (SortableJS)
            const list = document.getElementById('question-list');
            if(list) {
                new Sortable(list, {
                    handle: '.handle',
                    animation: 150,
                    onEnd: function (evt) {
                        const items = Array.from(list.querySelectorAll('li.list-group-item'));
                        const order = items.map(item => item.getAttribute('data-id'));
                        
                        fetch('{{ route("surveys.questions.reorder", $survey) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: order })
                        }).then(response => response.json()).then(data => {
                            if(data.success) {
                                items.forEach((item, idx) => {
                                    const h6 = item.querySelector('h6');
                                    h6.innerHTML = (idx + 1) + '. ' + h6.innerHTML.substring(h6.innerHTML.indexOf('. ') + 2);
                                });
                            }
                        });
                    },
                });
            }
        });

        function syncCorrectHidden(cb) {
            const type = document.getElementById('questionType').value;
            if (type === 'radio') {
                // Untuk radio: saat satu opsi dipilih, set nilainya jadi 1 dan force reset semua opsi lain jadi 0
                document.querySelectorAll('.option-correct-checkbox').forEach(input => {
                    const hidden = input.parentElement.querySelector('.option-correct-hidden');
                    if (hidden) {
                        hidden.value = (input === cb && cb.checked) ? '1' : '0';
                    }
                });
            } else {
                const hidden = cb.parentElement.querySelector('.option-correct-hidden');
                if (hidden) hidden.value = cb.checked ? '1' : '0';
            }
        }

        function addOptionRow(val = '', score = 0, isCorrect = false) {
            const container = document.getElementById('optionsContainer');
            const type = document.getElementById('questionType').value;
            const inputType = type === 'radio' ? 'radio' : 'checkbox';
            const inputName = type === 'radio' ? 'name="option_correct_radio_ui"' : '';
            const checkedStr = isCorrect ? 'checked' : '';
            const hiddenVal = isCorrect ? '1' : '0';
            const html = `
                <div class="input-group mb-2 option-row">
                    <label class="input-group-text bg-light border-end-0 px-3 d-flex align-items-center mb-0 user-select-none" title="Klik untuk menconteng sebagai Kunci Jawaban" style="cursor:pointer;">
                        <input class="form-check-input mt-0 option-correct-checkbox me-2" type="${inputType}" ${inputName} onchange="syncCorrectHidden(this)" ${checkedStr} aria-label="Kunci" style="cursor:pointer;">
                        <input type="hidden" name="options_correct[]" value="${hiddenVal}" class="option-correct-hidden">
                        <i class="ri-key-2-line text-primary me-1 fs-15"></i>
                        <span class="fw-medium text-dark fs-13">Kunci</span>
                    </label>
                    <input type="text" name="options[]" class="form-control" placeholder="Tuliskan teks opsi jawaban..." value="${val}" required>
                    <input type="hidden" name="options_score[]" value="100">
                    <button class="btn btn-danger-light btn-icon" type="button" onclick="removeOptionRow(this)" title="Hapus Opsi"><i class="ri-close-line fs-16"></i></button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeOptionRow(btn) {
            const rows = document.querySelectorAll('.option-row');
            if(rows.length > 2) {
                btn.closest('.option-row').remove();
            } else {
                if (typeof BpkpSwal !== 'undefined') {
                    BpkpSwal.alert('Tipe Pilihan Ganda dan Kotak Centang wajib memiliki minimal 2 opsi jawaban! Opsi ini tidak dapat dihapus.', 'Tidak Dapat Menghapus', 'warning');
                } else {
                    alert('Tipe Pilihan Ganda dan Kotak Centang wajib memiliki minimal 2 opsi jawaban! Opsi ini tidak dapat dihapus.');
                }
            }
        }

        function openCreateModal() {
            document.getElementById('questionForm').action = '{{ route("surveys.questions.store", $survey) }}';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('modalTitle').textContent = '{{ __("Tambah Pertanyaan") }}';
            
            document.getElementById('questionType').value = 'radio';
            document.getElementById('questionText').value = '';
            document.getElementById('isRequired').checked = true;
            
            document.getElementById('optionsContainer').innerHTML = '';
            addOptionRow('', 0, false);
            addOptionRow('', 0, false);
            
            document.getElementById('questionType').dispatchEvent(new Event('change'));
        }

        function openEditModal(question, options) {
            document.getElementById('questionForm').action = `/surveys/{{ $survey->id }}/questions/${question.id}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('modalTitle').textContent = '{{ __("Edit Pertanyaan") }}';
            
            document.getElementById('questionType').value = question.type;
            document.getElementById('questionText').value = question.question_text;
            document.getElementById('isRequired').checked = question.is_required ? true : false;
            
            document.getElementById('optionsContainer').innerHTML = '';
            if (options && options.length > 0) {
                options.forEach(opt => addOptionRow(opt.option_text, opt.score_value || 0, opt.is_correct || false));
            } else {
                addOptionRow('', 0, false);
                addOptionRow('', 0, false);
            }
            
            document.getElementById('questionType').dispatchEvent(new Event('change'));
            
            new bootstrap.Modal(document.getElementById('questionModal')).show();
        }
    </script>
@endpush
