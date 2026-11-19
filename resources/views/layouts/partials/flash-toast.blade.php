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
    } elseif (isset($errors) && $errors->has('berkas_file')) {
        // Error unggah berkas/H5P dari modal aktivitas (modal tertutup setelah redirect).
        $toastMessage = $errors->first('berkas_file');
        $toastVariant = 'danger';
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
