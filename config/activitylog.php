<?php

use App\Models\ActivityLog;
use App\Support\Activity\EnrichedLogActivityAction;
use Spatie\Activitylog\Actions\CleanActivityLogAction;

return [

    'enabled' => env('ACTIVITYLOG_ENABLED', true),

    /*
     * Dipakai oleh "php artisan activitylog:clean" dan tombol pembersihan manual
     * di halaman Log Aktivitas (Super Admin). Tidak ada penghapusan otomatis.
     */
    'clean_after_days' => (int) env('ACTIVITYLOG_RETENTION_DAYS', 90), // 3 bulan

    'default_log_name' => 'data',

    'default_auth_driver' => null,

    'include_soft_deleted_subjects' => false,

    // Model Activity yang diperluas (kolom konteks request + scope isolasi per user).
    'activity_model' => ActivityLog::class,

    /*
     * Atribut yang TIDAK PERNAH dicatat untuk model apa pun.
     * Masking tambahan (pola password/token/secret/api key) ada di EnrichedLogActivityAction.
     */
    'default_except_attributes' => [
        // "password" sengaja TIDAK dikecualikan: perubahannya tercatat, nilainya disamarkan.
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ],

    'buffer' => [
        'enabled' => env('ACTIVITYLOG_BUFFER_ENABLED', false),
    ],

    'actions' => [
        'log_activity' => EnrichedLogActivityAction::class,
        'clean_log' => CleanActivityLogAction::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log request (aktivitas halaman & aksi)
    |--------------------------------------------------------------------------
    | Semua request user yang sudah login dicatat, KECUALI route berfrekuensi
    | sangat tinggi di bawah. Hasil pentingnya tetap tercatat lewat perubahan data
    | (mis. heartbeat video tidak dicatat, tapi "materi selesai" tercatat).
    */
    'ignore_routes' => [
        'peserta.kursus.lessons.progress', // heartbeat video/PDF tiap 5 detik
        'h5p.assets',                       // file aset paket H5P
        'ai-assistant.quota',               // polling sisa kuota
    ],

    /*
     * Route yang hanya diabaikan bila dipanggil otomatis di latar belakang (AJAX/JSON),
     * mis. dropdown notifikasi di header. Bila dibuka user sebagai halaman, tetap dicatat.
     */
    'ignore_background_routes' => [
        'notifications.index',
    ],

    // Nilai payload lebih panjang dari ini dipotong.
    'max_value_length' => 500,
];
