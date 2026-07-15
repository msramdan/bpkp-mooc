<?php
$user = \App\Models\User::where('email', 'admin@example.com')->first();
$user->assignRole('super_admin');
echo "Admin created successfully!\n";
