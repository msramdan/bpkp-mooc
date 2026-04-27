@extends('layouts.auth')

@section('title', __('Sign In') . ' | ' . config('app.brand_display'))

@section('content')
    {{-- Struktur seperti signin-basic.html: .container tanpa z-index (agar canvas partikel di belakang tetap interaktif di area kosong). Hanya blok konten pakai z-3 di atas canvas. --}}
    <div class="container">
        <div class="row justify-content-center authentication authentication-basic align-items-center h-100 py-4">
            <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                <div class="position-relative z-3">
                {{-- Identitas aplikasi di atas kartu --}}
                <div class="mb-3 text-center px-2">
                    <a href="{{ route('landing-page.index') }}"
                        class="text-decoration-none d-inline-block text-white">
                        <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                            <i class="bi bi-mortarboard-fill fs-2 lh-1" aria-hidden="true"></i>
                            <span class="fw-bold fs-3 lh-sm">{{ config('app.brand_display') }}</span>
                        </div>
                        <p class="small text-white mt-2 mb-0 opacity-75">{{ __('LMS kursus Pusdiklatwas.') }}</p>
                    </a>
                </div>

                <div class="card custom-card my-4 border position-relative">
                    <div class="card-body p-0">
                        <div class="p-5">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <span class="auth-icon text-primary" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="48" height="48"
                                        fill="currentColor">
                                        <path
                                            d="M59,8H5A1,1,0,0,0,4,9V55a1,1,0,0,0,1,1H59a1,1,0,0,0,1-1V9A1,1,0,0,0,59,8ZM58,54H6V10H58Z" />
                                        <path
                                            d="M36,35H28a3,3,0,0,1-3-3V27a3,3,0,0,1,3-3h8a3,3,0,0,1,3,3v5A3,3,0,0,1,36,35Zm-8-9a1,1,0,0,0-1,1v5a1,1,0,0,0,1,1h8a1,1,0,0,0,1-1V27a1,1,0,0,0-1-1Z" />
                                        <path
                                            d="M36 26H28a1 1 0 0 1-1-1V24a5 5 0 0 1 10 0v1A1 1 0 0 1 36 26zm-7-2h6a3 3 0 0 0-6 0zM32 31a1 1 0 0 1-1-1V29a1 1 0 0 1 2 0v1A1 1 0 0 1 32 31z" />
                                        <path
                                            d="M59 8H5A1 1 0 0 0 4 9v8a1 1 0 0 0 1 1H20.08a1 1 0 0 0 .63-.22L25.36 14H59a1 1 0 0 0 1-1V9A1 1 0 0 0 59 8zm-1 4H25l-.21 0a1.09 1.09 0 0 0-.42.2L19.73 16H6V10H58zM50 49H14a1 1 0 0 1-1-1V39a1 1 0 0 1 1-1H50a1 1 0 0 1 1 1v9A1 1 0 0 1 50 49zM15 47H49V40H15z" />
                                        <circle cx="19.5" cy="43.5" r="1.5" />
                                        <circle cx="24.5" cy="43.5" r="1.5" />
                                        <circle cx="29.5" cy="43.5" r="1.5" />
                                        <circle cx="34.5" cy="43.5" r="1.5" />
                                        <circle cx="39.5" cy="43.5" r="1.5" />
                                        <circle cx="44.5" cy="43.5" r="1.5" />
                                        <path
                                            d="M60 9a1 1 0 0 0-1-1H28.81l2.37-2.37A19.22 19.22 0 0 1 60 31zM35.19 56l-2.37 2.37A19.22 19.22 0 0 1 4 33V55a1 1 0 0 0 1 1z"
                                            opacity=".3" />
                                    </svg>
                                </span>
                            </div>
                            <p class="h4 fw-semibold mb-0 text-center">{{ __('Sign In') }}</p>
                            <p class="mb-4 text-muted fw-normal text-center">
                                {{ __('Sign in to continue to Application.') }}</p>

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    <ul class="mb-0 ps-3 small">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('status'))
                                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                            @endif

                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="row gy-3">
                                    <div class="col-xl-12">
                                        <label for="login-email" class="form-label text-default">{{ __('Email') }}</label>
                                        <div class="position-relative">
                                            <input type="email" name="email" id="login-email" value="{{ old('email') }}"
                                                class="form-control form-control-lg @error('email') is-invalid @enderror"
                                                placeholder="{{ __('Email') }}" autocomplete="username" required
                                                autofocus>
                                        </div>
                                        @error('email')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-xl-12 mb-2">
                                        <label for="signin-password" class="form-label text-default">{{ __('Password') }}</label>
                                        <div class="position-relative">
                                            <input type="password" name="password" id="signin-password"
                                                class="form-control form-control-lg form-control-password-toggle @error('password') is-invalid @enderror"
                                                placeholder="{{ __('Password') }}" autocomplete="current-password"
                                                required>
                                            <button type="button" class="show-password-button text-muted"
                                                onclick="createpassword('signin-password',this)" id="button-addon2"
                                                aria-label="{{ __('Show password') }}"><i class="ri-eye-off-line align-middle"></i></button>
                                        </div>
                                        @error('password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remember"
                                                    id="remember" value="on" @checked(old('remember'))>
                                                <label class="form-check-label text-muted fw-normal fs-12"
                                                    for="remember">
                                                    {{ __('Remember me') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-lg btn-primary">{{ __('Sign In') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <p class="text-center small mt-3 mb-0 text-white" style="opacity: 0.75;">
                    &copy; {{ now()->year }} {{ config('app.brand_display') }}
                </p>
                </div>
            </div>
        </div>
    </div>
@endsection
