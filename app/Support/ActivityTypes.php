<?php

namespace App\Support;

/**
 * Simplified activity palette for the new MOOC (mapped from Moodle / mooc11f).
 *
 * Enabled for create: berkas, video, url.
 * Other types remain visible but disabled until implemented.
 */
class ActivityTypes
{
    public const ENABLED = ['berkas', 'video', 'url'];

    /**
     * Legacy Moodle/import types → simplified keys.
     *
     * @var array<string, string>
     */
    public const LEGACY_MAP = [
        'dokumen' => 'berkas',
        'reading' => 'berkas',
        'live' => 'url',
        'kuis' => 'pre_test',
    ];

    /**
     * @return list<array{key: string, label: string, enabled: bool, icon: string, color: string}>
     */
    public static function palette(): array
    {
        return [
            ['key' => 'pre_test', 'label' => 'Pre-Test', 'enabled' => false, 'icon' => 'bi-clipboard-check', 'color' => '#16a34a'],
            ['key' => 'berkas', 'label' => 'Berkas', 'enabled' => true, 'icon' => 'bi-file-earmark', 'color' => '#2563eb'],
            ['key' => 'video', 'label' => 'Video', 'enabled' => true, 'icon' => 'bi-play-btn', 'color' => '#dc2626'],
            ['key' => 'h5p', 'label' => 'H5P', 'enabled' => false, 'icon' => 'bi-puzzle', 'color' => '#ca8a04'],
            ['key' => 'scorm', 'label' => 'SCORM', 'enabled' => false, 'icon' => 'bi-box', 'color' => '#65a30d'],
            ['key' => 'url', 'label' => 'URL', 'enabled' => true, 'icon' => 'bi-link-45deg', 'color' => '#0284c7'],
            ['key' => 'penugasan', 'label' => 'Penugasan', 'enabled' => false, 'icon' => 'bi-pencil-square', 'color' => '#7c3aed'],
            ['key' => 'forum', 'label' => 'Forum', 'enabled' => false, 'icon' => 'bi-chat-dots', 'color' => '#9333ea'],
            ['key' => 'survey', 'label' => 'Survey/Kuesioner', 'enabled' => false, 'icon' => 'bi-bar-chart', 'color' => '#0891b2'],
            ['key' => 'post_test', 'label' => 'Post-Test', 'enabled' => false, 'icon' => 'bi-clipboard2-check', 'color' => '#ea580c'],
            ['key' => 'sertifikat', 'label' => 'Sertifikat', 'enabled' => false, 'icon' => 'bi-award', 'color' => '#d97706'],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_column(self::palette(), 'key');
    }

    /** @return list<string> */
    public static function enabledKeys(): array
    {
        return self::ENABLED;
    }

    public static function isEnabled(string $key): bool
    {
        return in_array($key, self::ENABLED, true);
    }

    public static function normalize(string $tipe): string
    {
        $tipe = strtolower(trim($tipe));

        return self::LEGACY_MAP[$tipe] ?? $tipe;
    }

    public static function label(string $tipe): string
    {
        $key = self::normalize($tipe);

        foreach (self::palette() as $item) {
            if ($item['key'] === $key) {
                return __($item['label']);
            }
        }

        return __(ucfirst($key));
    }

    public static function icon(string $tipe): string
    {
        $key = self::normalize($tipe);

        foreach (self::palette() as $item) {
            if ($item['key'] === $key) {
                return $item['icon'];
            }
        }

        return 'bi-circle';
    }

    public static function color(string $tipe): string
    {
        $key = self::normalize($tipe);

        foreach (self::palette() as $item) {
            if ($item['key'] === $key) {
                return $item['color'];
            }
        }

        return '#64748b';
    }

    /** @return array{key: string, label: string, enabled: bool, icon: string, color: string}|null */
    public static function find(string $key): ?array
    {
        $key = self::normalize($key);

        foreach (self::palette() as $item) {
            if ($item['key'] === $key) {
                return $item;
            }
        }

        return null;
    }
}
