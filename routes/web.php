<?php

use App\Support\Roles;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\CourseLessonController;
use App\Http\Controllers\CourseModuleController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\LearningCategoryController;
use App\Http\Controllers\LearningTagController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Peserta\CatalogEnrollmentController;
use App\Http\Controllers\Peserta\CertificateController as PesertaCertificateController;
use App\Http\Controllers\Peserta\LessonController as PesertaLessonController;
use App\Http\Controllers\Peserta\PortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', [LandingPageController::class, 'index'])->name('landing-page.index');

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');

    Route::get('/dashboard', HomeController::class)->name('dashboard');

    Route::middleware('role:peserta')->prefix('peserta')->name('peserta.')->group(function () {
        Route::get('/dashboard', [PortalController::class, 'dashboard'])
            ->middleware('permission:peserta dashboard view')
            ->name('dashboard');
        Route::get('/kursus', [PortalController::class, 'kursus'])->middleware('permission:peserta kursus view')->name('kursus.index');
        Route::get('/kursus/{course}', [PortalController::class, 'kursusShow'])->middleware('permission:peserta kursus view')->name('kursus.show');
        Route::get('/kursus/{course}/materi/{lesson}', [PesertaLessonController::class, 'show'])->middleware('permission:peserta kursus view')->name('kursus.lessons.show');
        Route::post('/kursus/{course}/materi/{lesson}/selesai', [PesertaLessonController::class, 'complete'])->middleware('permission:peserta kursus view')->name('kursus.lessons.complete');
        Route::get('/katalog', [PortalController::class, 'katalog'])->middleware('permission:peserta katalog view')->name('katalog.index');
        Route::post('/katalog/{course}/daftar', [CatalogEnrollmentController::class, 'store'])->middleware('permission:peserta katalog view')->name('katalog.enroll');
        Route::get('/progres', [PortalController::class, 'progres'])->middleware('permission:peserta progres view')->name('progres.index');
        Route::get('/sertifikat', [PortalController::class, 'sertifikat'])->middleware('permission:peserta sertifikat view')->name('sertifikat.index');
        Route::get('/sertifikat/{certificate}', [PesertaCertificateController::class, 'show'])->middleware('permission:peserta sertifikat view')->name('sertifikat.show');
        Route::post('/kursus/{course}/sertifikat', [PesertaCertificateController::class, 'issueFromEnrollment'])->middleware('permission:peserta sertifikat view')->name('sertifikat.issue');
    });

    Route::middleware('role:'.Roles::SUPER_ADMIN)->group(function () {
        Route::resource('users', App\Http\Controllers\UserController::class);
        Route::resource('roles', App\Http\Controllers\RoleAndPermissionController::class);
        Route::resource('courses', CourseController::class);
        Route::post('courses/{course}/enrollments', [CourseEnrollmentController::class, 'store'])
            ->name('courses.enrollments.store');
        Route::delete('courses/{course}/enrollments/{enrollment}', [CourseEnrollmentController::class, 'destroy'])
            ->name('courses.enrollments.destroy');

        Route::post('courses/{course}/modules', [CourseModuleController::class, 'store'])->name('courses.modules.store');
        Route::put('courses/{course}/modules/{module}', [CourseModuleController::class, 'update'])->name('courses.modules.update');
        Route::delete('courses/{course}/modules/{module}', [CourseModuleController::class, 'destroy'])->name('courses.modules.destroy');
        Route::post('courses/{course}/modules/{module}/lessons', [CourseLessonController::class, 'store'])->name('courses.modules.lessons.store');
        Route::put('courses/{course}/modules/{module}/lessons/{lesson}', [CourseLessonController::class, 'update'])->name('courses.modules.lessons.update');
        Route::delete('courses/{course}/modules/{module}/lessons/{lesson}', [CourseLessonController::class, 'destroy'])->name('courses.modules.lessons.destroy');

        Route::resource('learning-categories', LearningCategoryController::class);
        Route::resource('learning-tags', LearningTagController::class);
        Route::get('database-backups', [DatabaseBackupController::class, 'index'])->name('database-backups.index');
        Route::post('database-backups/download', [DatabaseBackupController::class, 'download'])->name('database-backups.download');
    });
});

Route::middleware(['auth', 'permission:test view'])->get('/tests', function () {
    dd('This is just a test and an example for permission and sidebar menu. You can remove this line on web.php, config/permission.php and config/generator.php');
})->name('tests.index');
