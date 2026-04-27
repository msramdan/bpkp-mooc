<div class="row gy-3">
    <div class="col-md-6">
        <label class="form-label" for="name">{{ __('Name') }}</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
            placeholder="{{ __('Name') }}" value="{{ isset($user) ? $user->name : old('name') }}" required autofocus>
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">{{ __('Email') }}</label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
            placeholder="{{ __('Email') }}" value="{{ isset($user) ? $user->email : old('email') }}" required>
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
                @isset($user)
                    <option value="{{ $role->id }}" @selected($user->getRoleNames()->isNotEmpty() && $user->getRoleNames()->first() === $role->name)>
                        {{ $role->name }}
                    </option>
                @else
                    <option value="{{ $role->id }}" @selected((string) old('role') === (string) $role->id)>{{ $role->name }}</option>
                @endisset
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
                <img src="{{ isset($user) ? $user->avatar : 'https://placehold.co/120x120?text=Avatar' }}"
                    alt="{{ __('Avatar') }}"
                    class="rounded img-fluid js-avatar-preview"
                    style="object-fit: cover; width: 120px; height: 120px; max-width: 100%;"
                    data-placeholder="https://placehold.co/120x120?text=Avatar">
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
