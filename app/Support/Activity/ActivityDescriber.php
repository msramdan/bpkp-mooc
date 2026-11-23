<?php

namespace App\Support\Activity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

/**
 * Menerjemahkan route & model menjadi kalimat aktivitas yang mudah dibaca.
 * Route yang belum dipetakan tetap tercatat dengan deskripsi generik (method + path).
 */
class ActivityDescriber
{
    /** Template per nama route. Placeholder :param diganti label model/route parameter. */
    private const ROUTES = [
        // Umum
        'login.store' => 'Mengirim form login',
        'logout' => 'Logout',
        'password.confirm.store' => 'Konfirmasi password',
        'dashboard' => 'Membuka dashboard',
        'profile' => 'Membuka profil',
        'user-profile-information.update' => 'Memperbarui informasi profil',
        'user-password.update' => 'Mengganti password',
        'locale.update' => 'Mengganti bahasa',
        'courses.search' => 'Mencari kursus',
        'notifications.index' => 'Membuka notifikasi',
        'notifications.read' => 'Menandai notifikasi dibaca',
        'notifications.read-all' => 'Menandai semua notifikasi dibaca',
        'activity.mine' => 'Membuka riwayat aktivitas saya',
        'activity.mine.show' => 'Membuka detail aktivitas saya',

        // Peserta
        'peserta.dashboard' => 'Membuka beranda peserta',
        'peserta.kursus.index' => 'Membuka daftar kursus saya',
        'peserta.kursus.show' => 'Membuka kursus ":course"',
        'peserta.kursus.lessons.show' => 'Membuka materi ":lesson"',
        'peserta.kursus.lessons.complete' => 'Menandai selesai materi ":lesson"',
        'peserta.kursus.lessons.submit' => 'Mengumpulkan penugasan ":lesson"',
        'peserta.kursus.lessons.survey.submit' => 'Mengirim jawaban ":lesson"',
        'peserta.kursus.lessons.certificate.print' => 'Mencetak sertifikat kursus ":course"',
        'peserta.kursus.forum.index' => 'Membuka forum kursus ":course"',
        'peserta.kursus.forum.store' => 'Membuat diskusi forum di ":course"',
        'peserta.kursus.forum.update' => 'Mengubah diskusi forum',
        'peserta.kursus.forum.reply' => 'Membalas diskusi forum',
        'peserta.kursus.forum.reply.update' => 'Mengubah balasan forum',
        'peserta.kursus.forum.like' => 'Menyukai / batal menyukai diskusi forum',
        'peserta.katalog.index' => 'Membuka katalog kursus',
        'peserta.katalog.enroll' => 'Mendaftar kursus ":course"',
        'peserta.progres.index' => 'Membuka nilai & progres',
        'peserta.sertifikat.index' => 'Membuka daftar sertifikat',
        'peserta.sertifikat.show' => 'Membuka sertifikat',
        'peserta.sertifikat.issue' => 'Menerbitkan sertifikat kursus ":course"',

        // AI Assistant
        'ai-assistant.chat' => 'Membuka AI Assistant',
        'ai-assistant.ask' => 'Bertanya ke AI Assistant',
        'ai-assistant.conversations' => 'Membuka riwayat percakapan AI',
        'ai-assistant.conversations.show' => 'Membuka percakapan AI',

        // Admin: kursus
        'courses.index' => 'Membuka daftar kursus (admin)',
        'courses.create' => 'Membuka form tambah kursus',
        'courses.store' => 'Menambah kursus',
        'courses.show' => 'Membuka kursus ":course" (admin)',
        'courses.edit' => 'Membuka form ubah kursus ":course"',
        'courses.update' => 'Mengubah kursus ":course"',
        'courses.destroy' => 'Menghapus kursus ":course"',
        'courses.modules.store' => 'Menambah topik di kursus ":course"',
        'courses.modules.update' => 'Mengubah topik ":module"',
        'courses.modules.destroy' => 'Menghapus topik ":module"',
        'courses.modules.reorder' => 'Mengubah urutan topik kursus ":course"',
        'courses.modules.lessons.store' => 'Menambah aktivitas di topik ":module"',
        'courses.modules.lessons.update' => 'Mengubah aktivitas ":lesson"',
        'courses.modules.lessons.destroy' => 'Menghapus aktivitas ":lesson"',
        'courses.enrollments.store' => 'Mendaftarkan peserta ke kursus ":course"',
        'courses.enrollments.destroy' => 'Mengeluarkan peserta dari kursus ":course"',
        'courses.enrollments.bulk-destroy' => 'Mengeluarkan beberapa peserta dari kursus ":course"',
        'courses.enrollments.import' => 'Import peserta ke kursus ":course"',
        'courses.enrollments.export' => 'Export peserta kursus ":course"',
        'courses.enrollments.template' => 'Unduh template import peserta',
        'courses.enrollments.progress' => 'Melihat progres peserta di kursus ":course"',
        'courses.enrollments.available' => 'Mencari peserta untuk kursus ":course"',
        'courses.forum.store' => 'Membuat diskusi forum (admin) di ":course"',
        'courses.forum.reply' => 'Membalas forum (admin)',

        // Admin: master data
        'users.index' => 'Membuka daftar user',
        'users.create' => 'Membuka form tambah user',
        'users.store' => 'Menambah user',
        'users.show' => 'Melihat detail user ":user"',
        'users.edit' => 'Membuka form ubah user ":user"',
        'users.update' => 'Mengubah user ":user"',
        'users.destroy' => 'Menghapus user ":user"',
        'users.courses' => 'Melihat kursus user ":user"',
        'roles.index' => 'Membuka daftar peran',
        'roles.store' => 'Menambah peran',
        'roles.update' => 'Mengubah peran & izin',
        'roles.destroy' => 'Menghapus peran',
        'learning-categories.index' => 'Membuka kategori pembelajaran',
        'learning-categories.store' => 'Menambah kategori pembelajaran',
        'learning-categories.update' => 'Mengubah kategori pembelajaran',
        'learning-categories.destroy' => 'Menghapus kategori pembelajaran',
        'learning-categories.import' => 'Import kategori pembelajaran',
        'learning-categories.export' => 'Export kategori pembelajaran',
        'certificate-templates.index' => 'Membuka template sertifikat',
        'certificate-templates.store' => 'Menambah template sertifikat',
        'certificate-templates.update' => 'Mengubah template sertifikat',
        'certificate-templates.layout.update' => 'Mengubah tata letak template sertifikat',
        'certificate-templates.destroy' => 'Menghapus template sertifikat',
        'kompetensi-tags.refresh' => 'Menyegarkan tag kompetensi dari TNA',
        'database-backups.index' => 'Membuka backup database',
        'database-backups.download' => 'Mengunduh backup database',

        // Admin: survei
        'surveys.index' => 'Membuka bank soal / kuesioner',
        'surveys.store' => 'Menambah kuesioner',
        'surveys.update' => 'Mengubah kuesioner ":survey"',
        'surveys.destroy' => 'Menghapus kuesioner ":survey"',
        'surveys.builder' => 'Membuka penyusun soal ":survey"',
        'surveys.questions.store' => 'Menambah soal di ":survey"',
        'surveys.questions.update' => 'Mengubah soal di ":survey"',
        'surveys.questions.destroy' => 'Menghapus soal di ":survey"',
        'surveys.questions.reorder' => 'Mengubah urutan soal ":survey"',
        'surveys.import-pg.store' => 'Import soal pilihan ganda ke ":survey"',
        'surveys.recap.index' => 'Membuka rekap ":survey"',
        'surveys.recap.grade-essay' => 'Menilai esai peserta',
        'surveys.recap.reset-attempts' => 'Me-reset percobaan peserta',
        'surveys.recap.export-participants' => 'Export rekap peserta ":survey"',
        'surveys.recap.export-courses' => 'Export rekap kursus ":survey"',

        // Admin: AI Assistant & log
        'ai-assistant-logs.index' => 'Membuka monitoring AI',
        'ai-assistant-logs.users' => 'Membuka aktivitas AI per user',
        'ai-assistant-logs.users.show' => 'Membuka riwayat AI user ":user"',
        'ai-assistant-logs.requests' => 'Membuka log request AI',
        'ai-assistant-logs.requests.export' => 'Export log AI',
        'ai-assistant-logs.users.export' => 'Export aktivitas AI per user',
        'ai-assistant-settings.index' => 'Membuka AI Assistant Settings',
        'announcements.index' => 'Membuka kelola pengumuman',
        'announcements.create' => 'Membuka form tambah pengumuman',
        'announcements.store' => 'Membuat pengumuman',
        'announcements.edit' => 'Membuka form ubah pengumuman',
        'announcements.update' => 'Mengubah pengumuman',
        'announcements.toggle' => 'Menyalakan / mematikan pengumuman',
        'announcements.destroy' => 'Menghapus pengumuman',
        'announcements.feed' => 'Membuka daftar pengumuman',
        'announcements.dismiss' => 'Menutup pop up pengumuman',
        'announcements.attachment' => 'Membuka lampiran pengumuman',
        'activity-logs.index' => 'Membuka log aktivitas (admin)',
        'activity-logs.show' => 'Membuka detail log aktivitas',
        'activity-logs.export' => 'Export log aktivitas',
        'activity-logs.prune' => 'Membersihkan log aktivitas lama',
    ];

    private const MODELS = [
        'User' => 'User',
        'Course' => 'Kursus',
        'CourseModule' => 'Topik',
        'CourseLesson' => 'Materi / Aktivitas',
        'CourseEnrollment' => 'Pendaftaran Kursus',
        'CourseTag' => 'Tag Kursus',
        'LearningCategory' => 'Kategori Pembelajaran',
        'LessonCompletion' => 'Penyelesaian Materi',
        'AssignmentSubmission' => 'Pengumpulan Tugas',
        'Survey' => 'Kuesioner',
        'SurveyQuestion' => 'Soal',
        'SurveyOption' => 'Opsi Jawaban',
        'SurveyResponse' => 'Jawaban Kuesioner',
        'SurveyAnswer' => 'Isi Jawaban',
        'Certificate' => 'Sertifikat',
        'CertificateTemplate' => 'Template Sertifikat',
        'CourseForumThread' => 'Diskusi Forum',
        'CourseForumReply' => 'Balasan Forum',
        'CourseForumLike' => 'Suka Forum',
        'AiAssistantSetting' => 'Pengaturan AI Assistant',
        'Announcement' => 'Pengumuman',
    ];

    private const EVENTS = [
        'created' => 'Menambah',
        'updated' => 'Mengubah',
        'deleted' => 'Menghapus',
        'restored' => 'Memulihkan',
    ];

    public static function forRoute(?Route $route, string $method, string $path): string
    {
        $name = $route?->getName();

        if ($name && isset(self::ROUTES[$name])) {
            $template = self::ROUTES[$name];

            return preg_replace_callback('/:([a-z_]+)/', function ($m) use ($route) {
                $value = $route->parameter($m[1]);

                return $value instanceof Model ? Str::limit(self::modelName($value), 80) : (is_scalar($value) ? (string) $value : $m[0]);
            }, $template) ?? $template;
        }

        $verb = match (strtoupper($method)) {
            'GET', 'HEAD' => 'Membuka',
            'POST' => 'Mengirim',
            'PUT', 'PATCH' => 'Memperbarui',
            'DELETE' => 'Menghapus',
            default => strtoupper($method),
        };

        return $verb.' /'.ltrim($path, '/');
    }

    public static function forModel(Model $model, string $event): string
    {
        $verb = self::EVENTS[$event] ?? ucfirst($event);
        $name = self::modelName($model);

        return trim($verb.' '.mb_strtolower(self::modelLabel($model::class)).($name !== '' ? ' "'.Str::limit($name, 80).'"' : ''));
    }

    public static function modelLabel(string $class): string
    {
        return self::MODELS[class_basename($class)] ?? Str::headline(class_basename($class));
    }

    public static function modelName(Model $model): string
    {
        foreach (['judul', 'title', 'name', 'question_text', 'option_text', 'nomor', 'key', 'kode'] as $attr) {
            $v = $model->getAttribute($attr);
            if (is_string($v) && $v !== '') {
                return $v;
            }
        }

        return '';
    }

    public static function eventLabel(?string $event): string
    {
        return match ($event) {
            'created' => 'Tambah',
            'updated' => 'Ubah',
            'deleted' => 'Hapus',
            'request' => 'Halaman / Aksi',
            'login' => 'Login',
            'logout' => 'Logout',
            'failed' => 'Login gagal',
            'lockout' => 'Diblokir sementara',
            default => $event ? Str::headline($event) : '-',
        };
    }

    /** @return array<string, string> label model untuk filter */
    public static function modelOptions(): array
    {
        $out = [];
        foreach (self::MODELS as $base => $label) {
            $out['App\\Models\\'.$base] = $label;
        }
        asort($out);

        return $out;
    }
}
