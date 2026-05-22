<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\LearningCategoryController;
use App\Http\Controllers\LearningTagController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Peserta\PortalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', [LandingPageController::class, 'index'])->name('landing-page.index');

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');

    Route::middleware('role:peserta')->prefix('peserta')->name('peserta.')->group(function () {
        Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/kursus', [PortalController::class, 'kursus'])->middleware('permission:peserta kursus view')->name('kursus.index');
        Route::get('/kursus/{course}', [PortalController::class, 'kursusShow'])->middleware('permission:peserta kursus view')->name('kursus.show');
        Route::get('/katalog', [PortalController::class, 'katalog'])->middleware('permission:peserta katalog view')->name('katalog.index');
        Route::get('/tugas', [PortalController::class, 'tugas'])->middleware('permission:peserta tugas view')->name('tugas.index');
        Route::get('/ujian', [PortalController::class, 'ujian'])->middleware('permission:peserta ujian view')->name('ujian.index');
        Route::get('/progres', [PortalController::class, 'progres'])->middleware('permission:peserta progres view')->name('progres.index');
        Route::get('/sertifikat', [PortalController::class, 'sertifikat'])->middleware('permission:peserta sertifikat view')->name('sertifikat.index');
        Route::get('/jadwal', [PortalController::class, 'jadwal'])->middleware('permission:peserta jadwal view')->name('jadwal.index');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', App\Http\Controllers\UserController::class);
        Route::resource('roles', App\Http\Controllers\RoleAndPermissionController::class);
        Route::resource('learning-categories', LearningCategoryController::class);
        Route::resource('learning-tags', LearningTagController::class);
        Route::get('database-backups', [DatabaseBackupController::class, 'index'])->name('database-backups.index');
        Route::post('database-backups/download', [DatabaseBackupController::class, 'download'])->name('database-backups.download');
    });
});

Route::middleware(['auth', 'permission:test view'])->get('/tests', function () {
    dd('This is just a test and an example for permission and sidebar menu. You can remove this line on web.php, config/permission.php and config/generator.php');
})->name('tests.index');
