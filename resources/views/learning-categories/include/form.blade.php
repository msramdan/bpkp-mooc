<div class="row gy-3">
    <div class="col-md-6 col-xl-4">
        <label class="form-label" for="name">{{ __('Name') }}</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
            placeholder="{{ __('Name') }}" value="{{ isset($learningCategory) ? $learningCategory->name : old('name') }}" autofocus required>
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
