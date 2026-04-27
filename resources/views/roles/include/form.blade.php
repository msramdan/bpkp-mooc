<div class="row gy-3">
    <div class="col-md-6 col-xl-4">
        <label class="form-label" for="name">{{ __('Name') }}</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
            placeholder="{{ __('Name') }}" value="{{ isset($role) ? $role->name : old('name') }}" autofocus required>
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12">
        <label class="form-label mb-0">{{ __('Permissions') }}</label>
        @error('permissions')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    @foreach (config('permission.permissions') as $permission)
        <div class="col-md-6 col-xl-4">
            <div class="card custom-card border mb-0 h-100">
                <div class="card-header py-2">
                    <div class="card-title mb-0 fs-14">{{ ucwords($permission['group']) }}</div>
                </div>
                <div class="card-body py-3">
                    @foreach ($permission['access'] as $access)
                        @php $slug = str()->slug($access); @endphp
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="perm-{{ $slug }}"
                                name="permissions[]" value="{{ $access }}"
                                @checked(isset($role) && $role->hasPermissionTo($access))>
                            <label class="form-check-label" for="perm-{{ $slug }}">
                                {{ ucwords(__($access)) }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
