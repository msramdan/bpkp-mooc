<div>
    <label class="form-label" for="f-from">{{ __('Dari') }}</label>
    <input type="date" id="f-from" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
</div>
<div>
    <label class="form-label" for="f-to">{{ __('Sampai') }}</label>
    <input type="date" id="f-to" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
</div>
