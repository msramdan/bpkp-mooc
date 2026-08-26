<div class="row gy-3">
    <div class="col-md-6">
        <label class="form-label" for="name">{{ __('Name') }}</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
            placeholder="{{ __('Name') }}" value="{{ old('name', $user->name ?? '') }}" required autofocus>
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="nip">{{ __('NIP') }}</label>
        <input type="text" name="nip" id="nip" class="form-control @error('nip') is-invalid @enderror"
            placeholder="{{ __('Nomor Induk Pegawai (18 digit)') }}" value="{{ old('nip', $user->nip ?? '') }}"
            maxlength="18" inputmode="numeric">
        @error('nip')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <div class="form-text">{{ __('Opsional. Digunakan untuk import peserta dan sertifikat.') }}</div>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="pangkat_golongan">{{ __('Pangkat, Golongan') }}</label>
        <input type="text" name="pangkat_golongan" id="pangkat_golongan" class="form-control @error('pangkat_golongan') is-invalid @enderror"
            placeholder="{{ __('Cth: Penata, III/c') }}" value="{{ old('pangkat_golongan', $user->pangkat_golongan ?? '') }}">
        @error('pangkat_golongan')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="jabatan">{{ __('Jabatan') }}</label>
        <input type="text" name="jabatan" id="jabatan" class="form-control @error('jabatan') is-invalid @enderror"
            placeholder="{{ __('Cth: Auditor Ahli Muda') }}" value="{{ old('jabatan', $user->jabatan ?? '') }}">
        @error('jabatan')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="unit_organisasi">{{ __('Unit Organisasi') }}</label>
        <input type="text" name="unit_organisasi" id="unit_organisasi" class="form-control @error('unit_organisasi') is-invalid @enderror"
            placeholder="{{ __('Cth: Pusat Pendidikan dan Pelatihan Pengawasan') }}" value="{{ old('unit_organisasi', $user->unit_organisasi ?? '') }}">
        @error('unit_organisasi')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">{{ __('Email') }}</label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
            placeholder="{{ __('Email') }}" value="{{ old('email', $user->email ?? '') }}" required>
        @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="password">{{ __('Password') }}</label>
        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
            placeholder="{{ __('Password') }}" {{ empty($user) ? 'required' : '' }} autocomplete="new-password">
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        @isset($user)
            <div class="form-text">{{ __('Leave the password & password confirmation blank if you don`t want to change them.') }}</div>
        @endisset
    </div>
    <div class="col-md-6">
        <label class="form-label" for="password-confirmation">{{ __('Password Confirmation') }}</label>
        <input type="password" name="password_confirmation" id="password-confirmation" class="form-control"
            placeholder="{{ __('Password Confirmation') }}" {{ empty($user) ? 'required' : '' }} autocomplete="new-password">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="role">{{ __('Role') }}</label>
        <select class="form-select @error('role') is-invalid @enderror" name="role" id="role" required>
            @unless (isset($user))
                <option value="" disabled @if (! old('role')) selected @endif>{{ __('-- Select role --') }}</option>
            @endunless
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected((string) old('role', isset($user) && $user->roles->isNotEmpty() ? $user->roles->first()->id : '') === (string) $role->id)>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
        @error('role')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label d-block mb-2">{{ __('Avatar') }}</label>
        <div class="d-flex flex-column flex-sm-row align-items-start gap-3">
            <div class="border rounded p-2 bg-light text-center" style="min-width: 120px;">
                <img src="{{ isset($user) && $user->avatar ? $user->avatar : asset('backend/assets/images/faces/2.jpg') }}"
                    alt="{{ __('Avatar') }}"
                    class="rounded img-fluid js-avatar-preview"
                    style="object-fit: cover; width: 120px; height: 120px; max-width: 100%;"
                    data-placeholder="{{ asset('backend/assets/images/faces/2.jpg') }}">
            </div>
            <div class="flex-grow-1 w-100">
                <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" id="avatar" accept="image/*">
                @error('avatar')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @isset($user)
                    <div class="form-text">{{ __('Leave the avatar blank if you don`t want to change it.') }}</div>
                @endisset
            </div>
        </div>
    </div>
</div>
