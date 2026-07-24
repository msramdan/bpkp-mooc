@extends('layouts.app')

@section('title', __('Survey Builder: ') . $survey->title)

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Pembangun Kuesioner') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('surveys.index') }}">{{ __('Bank Survey') }}</a></li>
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
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Hapus pertanyaan ini?')">
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
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="{{ $question->type }}" disabled>
                                                    <label class="form-check-label">{{ $option->option_text }}</label>
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
                <form id="questionForm" method="POST" action="{{ route('surveys.questions.store', $survey) }}">
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
                            
                            <!-- Area Opsi Jawaban (Hanya untuk Radio & Checkbox) -->
                            <div class="col-12" id="optionsArea">
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">{{ __('Pilihan Jawaban') }}</label>
                                    <button type="button" class="btn btn-sm btn-light btn-wave" onclick="addOptionRow()">
                                        <i class="ri-add-line"></i> {{ __('Tambah Opsi') }}
                                    </button>
                                </div>
                                <div id="optionsContainer">
                                    <div class="input-group mb-2 option-row">
                                        <input type="text" name="options[]" class="form-control" placeholder="Teks opsi jawaban..." required>
                                        <button class="btn btn-danger-light btn-icon" type="button" onclick="removeOptionRow(this)"><i class="ri-close-line"></i></button>
                                    </div>
                                    <div class="input-group mb-2 option-row">
                                        <input type="text" name="options[]" class="form-control" placeholder="Teks opsi jawaban..." required>
                                        <button class="btn btn-danger-light btn-icon" type="button" onclick="removeOptionRow(this)"><i class="ri-close-line"></i></button>
                                    </div>
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
            
            function toggleOptions() {
                if (['radio', 'checkbox'].includes(typeSelect.value)) {
                    optionsArea.style.display = 'block';
                    document.querySelectorAll('input[name="options[]"]').forEach(el => el.required = true);
                } else {
                    optionsArea.style.display = 'none';
                    document.querySelectorAll('input[name="options[]"]').forEach(el => el.required = false);
                }
            }
            
            typeSelect.addEventListener('change', toggleOptions);
            toggleOptions();

            // Drag and Drop (SortableJS)
            const list = document.getElementById('question-list');
            if(list) {
                new Sortable(list, {
                    handle: '.handle',
                    animation: 150,
                    onEnd: function (evt) {
                        const items = Array.from(list.querySelectorAll('li.list-group-item'));
                        const order = items.map(item => item.getAttribute('data-id'));
                        
                        // Kirim AJAX ke server untuk simpan urutan
                        fetch('{{ route("surveys.questions.reorder", $survey) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: order })
                        }).then(response => response.json()).then(data => {
                            if(data.success) {
                                // Update penomoran visual
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

        function addOptionRow(val = '') {
            const container = document.getElementById('optionsContainer');
            const html = `
                <div class="input-group mb-2 option-row">
                    <input type="text" name="options[]" class="form-control" placeholder="Teks opsi jawaban..." value="${val}" required>
                    <button class="btn btn-danger-light btn-icon" type="button" onclick="removeOptionRow(this)"><i class="ri-close-line"></i></button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeOptionRow(btn) {
            const rows = document.querySelectorAll('.option-row');
            if(rows.length > 1) {
                btn.closest('.option-row').remove();
            } else {
                alert('Minimal harus ada 1 opsi jawaban.');
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
            addOptionRow();
            addOptionRow();
            
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
                options.forEach(opt => addOptionRow(opt.option_text));
            } else {
                addOptionRow();
                addOptionRow();
            }
            
            document.getElementById('questionType').dispatchEvent(new Event('change'));
            
            new bootstrap.Modal(document.getElementById('questionModal')).show();
        }
    </script>
@endpush
