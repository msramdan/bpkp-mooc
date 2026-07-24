<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Super Admin (satu pintu administrasi MOOC)
    |--------------------------------------------------------------------------
    */
    'super_admin' => [
        'name' => env('SUPER_ADMIN_NAME', 'Admin'),
        'email' => env('SUPER_ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('SUPER_ADMIN_PASSWORD', 'password'),
    ],

];
