@extends('layouts.app')

@section('title', __('AI Assistant'))

@section('content')
    @php
        $homeRoute = auth()->user()->hasRole('peserta') ? 'peserta.dashboard' : 'dashboard';
    @endphp

    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('AI Assistant') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route($homeRoute) }}">{{ __('Beranda') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('AI Assistant') }}</li>
            </ol>
        </div>
    </div>

    @if (! $access['allowed'])
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <span class="avatar avatar-xl bg-secondary-transparent text-secondary mb-3">
                    <i class="ri-robot-2-line fs-28"></i>
                </span>
                <h5 class="fw-semibold mb-2">{{ __('AI Assistant tidak tersedia') }}</h5>
                <p class="text-muted mb-0">{{ $access['reason'] }}</p>
                @if ($conversations->isNotEmpty())
                    <p class="text-muted small mt-2 mb-0">{{ __('Riwayat percakapan Anda tetap tersimpan.') }}</p>
                @endif
            </div>
        </div>
    @else
        <div class="ai-chat" id="aiChat"
            data-ask-url="{{ route('ai-assistant.ask') }}"
            data-conversations-url="{{ route('ai-assistant.conversations') }}"
            data-conversation-url="{{ url('ai-assistant/conversations') }}"
            data-quota-url="{{ route('ai-assistant.quota') }}"
            data-quota-exceeded="{{ $quotaExceededMessage }}">

            <aside class="ai-chat__sidebar" id="aiChatSidebar" aria-label="{{ __('Riwayat percakapan') }}">
                <div class="ai-chat__sidebar-head">
                    <button type="button" class="btn btn-primary w-100 ai-chat__new" data-ai-new>
                        <i class="ri-add-line me-1"></i>{{ __('Percakapan baru') }}
                    </button>
                </div>

                <div class="ai-quota" id="aiQuota" aria-live="polite">
                    <div class="ai-quota__title"><span>{{ __('Tiket hari ini') }}</span><span data-quota-fraction></span></div>
                    <div class="ai-quota__tickets" data-quota-tickets aria-hidden="true"></div>
                    <div class="ai-quota__meter d-none" data-quota-meter><span data-quota-bar></span></div>
                    <dl class="ai-quota__list">
                        <div><dt>{{ __('Sisa pertanyaan') }}</dt><dd data-quota-remaining>-</dd></div>
                        <div><dt>{{ __('Sudah digunakan') }}</dt><dd data-quota-used>-</dd></div>
                        <div><dt>{{ __('Reset kuota') }}</dt><dd data-quota-reset>-</dd></div>
                    </dl>
                </div>

                <div class="ai-chat__history-title">{{ __('Riwayat') }}</div>
                <ul class="ai-chat__history" id="aiHistory">
                    @forelse ($conversations as $conversation)
                        <li>
                            <button type="button" class="ai-chat__history-item" data-ai-open="{{ $conversation->id }}"
                                title="{{ $conversation->title }}">
                                <span class="ai-chat__history-text">{{ $conversation->title }}</span>
                                <small>{{ $conversation->last_message_at?->diffForHumans() }}</small>
                            </button>
                        </li>
                    @empty
                        <li class="ai-chat__history-empty">{{ __('Belum ada percakapan.') }}</li>
                    @endforelse
                </ul>
            </aside>

            <section class="ai-chat__main">
                <header class="ai-chat__header">
                    <button type="button" class="btn btn-icon btn-sm btn-light d-lg-none" data-ai-toggle-sidebar
                        aria-controls="aiChatSidebar" aria-label="{{ __('Riwayat percakapan') }}">
                        <i class="ri-chat-history-line"></i>
                    </button>
                    <span class="ai-chat__avatar"><i class="ri-sparkling-2-line"></i></span>
                    <div class="ai-chat__heading">
                        <h2 id="aiTitle">{{ __('Asisten Belajar MOOC') }}</h2>
                        <p>{{ __('Menjawab berdasarkan data pembelajaran akun Anda sendiri.') }}</p>
                    </div>
                    <span class="ai-chat__quota-pill" data-quota-pill></span>
                </header>

                <div class="ai-chat__messages" id="aiMessages" aria-live="polite">
                    <div class="ai-chat__welcome" id="aiWelcome">
                        <span class="ai-chat__welcome-icon"><i class="ri-sparkling-2-line"></i></span>
                        <h3>{{ __('Halo, :name!', ['name' => \Illuminate\Support\Str::of(auth()->user()->name)->before(' ')]) }}</h3>
                        <p>{{ __('Tanyakan seputar kursus, progres, tugas, nilai, atau sertifikat Anda.') }}</p>
                        <div class="ai-chat__suggestions">
                            @php $chipIcons = ['ri-book-open-line', 'ri-line-chart-line', 'ri-award-line', 'ri-checkbox-circle-line', 'ri-compass-3-line']; @endphp
                            @foreach ($suggestions as $i => $suggestion)
                                <button type="button" class="ai-chip" data-ai-suggest="{{ $suggestion }}"><i class="{{ $chipIcons[$i % count($chipIcons)] }}"></i><span>{{ $suggestion }}</span></button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <form class="ai-chat__composer" id="aiForm" autocomplete="off">
                    <div class="ai-chat__notice d-none" id="aiNotice" role="alert"></div>
                    <div class="ai-chat__input-wrap">
                        <textarea id="aiInput" rows="1" maxlength="{{ $maxLength }}" class="form-control"
                            placeholder="{{ __('Tulis pertanyaan Anda…') }}" aria-label="{{ __('Pertanyaan') }}"></textarea>
                        <button type="submit" class="btn btn-primary ai-chat__send" id="aiSend" aria-label="{{ __('Kirim') }}">
                            <i class="ri-send-plane-2-fill"></i>
                        </button>
                    </div>
                    <div class="ai-chat__hint">
                        {{ __('AI dapat keliru. Periksa kembali informasi penting di menu terkait.') }}
                        <span id="aiCounter">0/{{ $maxLength }}</span>
                    </div>
                </form>
            </section>
        </div>

        <script type="application/json" id="aiInitialQuota">@json($quota)</script>
    @endif
@endsection

@if ($access['allowed'])
    @push('scripts')
        <script src="{{ asset('backend') }}/assets/js/ai-assistant.js?v={{ @filemtime(public_path('backend/assets/js/ai-assistant.js')) ?: time() }}"></script>
    @endpush
@endif
