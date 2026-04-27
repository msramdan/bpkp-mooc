@extends('layouts.app')

@section('title', __('Profile'))

@section('content')
    <div class="page-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="mb-1">{{ __('Profile') }}</h3>
            <p class="text-muted mb-0 small">{{ __('Update your account details and password.') }}</p>
        </div>
        <nav aria-label="breadcrumb" class="mb-0">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Profile') }}</li>
            </ol>
        </nav>
    </div>

    <div class="page-content">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                <x-alert />

                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title mb-0">{{ __('Profile') }}</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('user-profile-information.update') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label" for="email">{{ __('E-mail Address') }}</label>
                                <input type="email" name="email"
                                    class="form-control @error('email', 'updateProfileInformation') is-invalid @enderror"
                                    id="email" placeholder="{{ __('E-mail Address') }}"
                                    value="{{ old('email', auth()->user()->email) }}" required>
                                @error('email', 'updateProfileInformation')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="name">{{ __('Name') }}</label>
                                <input type="text" name="name"
                                    class="form-control @error('name', 'updateProfileInformation') is-invalid @enderror"
                                    id="name" placeholder="{{ __('Name') }}"
                                    value="{{ old('name', auth()->user()->name) }}" required>
                                @error('name', 'updateProfileInformation')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row align-items-start g-3 mb-4">
                                <div class="col-auto">
                                    <div class="avatar avatar-xl">
                                        <img src="{{ auth()->user()->avatar }}" alt="{{ __('Avatar') }}">
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label" for="avatar">{{ __('Avatar') }}</label>
                                    <input type="file" name="avatar"
                                        class="form-control @error('avatar', 'updateProfileInformation') is-invalid @enderror"
                                        id="avatar">
                                    @error('avatar', 'updateProfileInformation')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ __('Update Profile') }}</button>
                        </form>
                    </div>
                </div>

                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title mb-0">{{ __('Change Password') }}</div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('user-password.update') }}">
                            @csrf
                            @method('put')

                            <div class="mb-3">
                                <label class="form-label" for="current_password">{{ __('Current Password') }}</label>
                                <input type="password" name="current_password"
                                    class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                    id="current_password" autocomplete="current-password" required>
                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="password">{{ __('New Password') }}</label>
                                <input type="password" name="password"
                                    class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                    id="password" autocomplete="new-password" required>
                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" autocomplete="new-password" required>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ __('Change Password') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
