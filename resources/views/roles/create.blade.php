@extends('layouts.app')

@section('title', __('Create Role'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Create Role') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">{{ __('Roles') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Create') }}</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title mb-0">{{ __('Create an new role.') }}</div>
                </div>
                <div class="card-body">
                    <x-alert />

                    <form action="{{ route('roles.store') }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        @include('roles.include.form')

                        <div class="d-flex flex-wrap gap-2 justify-content-end mt-4 pt-3 border-top">
                            <x-back-link :href="route('roles.index')" />
                            <button type="submit" class="btn btn-primary btn-wave">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
