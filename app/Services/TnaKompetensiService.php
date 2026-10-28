<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mengambil daftar nama kompetensi (Kamus Kompetensi SKJ) dari Aplikasi TNA
 * untuk dipakai sebagai tag kursus.
 */
class TnaKompetensiService
{
    private const CACHE_KEY = 'tna.kompetensi.names';

    /** @return array<int, string> */
    public function names(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $names = $this->fetchNames();

        // Gagal ambil data tidak di-cache supaya request berikutnya mencoba lagi.
        if ($names !== []) {
            Cache::put(self::CACHE_KEY, $names, now()->addMinutes((int) config('services.tna.cache_minutes', 60)));
        }

        return $names;
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<int, string> */
    private function fetchNames(): array
    {
        $url = rtrim((string) config('services.tna.url'), '/');
        $apiKey = (string) config('services.tna.api_key');

        if ($url === '' || $apiKey === '') {
            Log::warning('TNA_API_URL / TNA_API_KEY belum diisi, tag kompetensi tidak dapat dimuat.');

            return [];
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders(['X-API-KEY' => $apiKey])
                ->timeout(10)
                ->get($url.'/api/v1/kompetensi');

            if (! $response->successful() || ! $response->json('success')) {
                Log::warning('Gagal mengambil kompetensi dari TNA', [
                    'status' => $response->status(),
                    'message' => $response->json('message'),
                ]);

                return [];
            }

            return collect($response->json('data.kompetensi', []))
                ->pluck('nama_kompetensi')
                ->map(fn ($name) => trim((string) $name))
                ->filter()
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('Gagal menghubungi API kompetensi TNA: '.$e->getMessage());

            return [];
        }
    }
}
