@extends('layouts.app')

@section('title', __('Edit User'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Edit User') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('Users') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Edit') }}</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title mb-0">{{ __('Edit an user.') }}</div>
                </div>
                <div class="card-body">
                    <x-alert />

                    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        @csrf
                        @method('PUT')
                        @include('users.include.form')

                        <div class="d-flex flex-wrap gap-2 justify-content-end mt-4 pt-3 border-top">
                            <x-back-link :href="route('users.index')" />
                            <button type="submit" class="btn btn-primary btn-wave">{{ __('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var input = document.getElementById('avatar');
            var img = document.querySelector('.js-avatar-preview');
            if (!input || !img) return;
            var original = img.src;
            input.addEventListener('change', function() {
                if (input.files && input.files[0]) {
                    img.src = URL.createObjectURL(input.files[0]);
                } else {
                    img.src = original;
                }
            });
        });
    </script>
@endpush

