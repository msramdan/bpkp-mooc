@php
    $toastMessage = null;
    $toastVariant = 'success';

    if (session('success')) {
        $toastMessage = session('success');
    } elseif (session('error')) {
        $toastMessage = session('error');
        $toastVariant = 'danger';
    } elseif (session('status') === 'profile-information-updated') {
        $toastMessage = __('Profile information updated successfully.');
    } elseif (session('status') === 'password-updated') {
        $toastMessage = __('Password updated successfully.');
    } elseif (session('status') && is_string(session('status'))) {
        $toastMessage = session('status');
    }
@endphp

@if ($toastMessage)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof BpkpBootstrapToast === 'undefined') {
                return;
            }
            BpkpBootstrapToast.show(@json($toastMessage), @json($toastVariant), @json(config('app.name')), {
                delay: 5500
            });
        });
    </script>
@endif

@include('partials.js.bpkp-swal-delete-confirm')
