<?php

namespace App\Support\Activity;

use Illuminate\Http\UploadedFile;

/**
 * Menyamarkan nilai sensitif sebelum disimpan ke log aktivitas.
 */
class Sanitizer
{
    public const MASK = '[disamarkan]';

    private const SENSITIVE_KEY = '/(pass(word)?|secret|token|api[_-]?key|remember|credential|authorization|cookie|private[_-]?key|recovery[_-]?code)/i';

    public static function isSensitiveKey(string|int $key): bool
    {
        return is_string($key) && (bool) preg_match(self::SENSITIVE_KEY, $key);
    }

    /**
     * Bersihkan array secara rekursif: kunci sensitif disamarkan, file diganti
     * metadata, string panjang dipotong, nilai rahasia berpola (sk-..., base64:...) disensor.
     */
    public static function clean(mixed $data, int $depth = 0): mixed
    {
        $max = (int) config('activitylog.max_value_length', 500);

        if ($depth > 6) {
            return '[terlalu dalam]';
        }

        if ($data instanceof UploadedFile) {
            return ['file' => $data->getClientOriginalName(), 'size' => $data->getSize(), 'mime' => $data->getClientMimeType()];
        }

        if (is_array($data)) {
            $out = [];
            $i = 0;
            foreach ($data as $key => $value) {
                if (++$i > 200) {
                    $out['…'] = 'dipotong ('.count($data).' item)';
                    break;
                }
                $out[$key] = self::isSensitiveKey($key) ? self::MASK : self::clean($value, $depth + 1);
            }

            return $out;
        }

        if (is_string($data)) {
            $data = preg_replace('/\b(sk-[A-Za-z0-9_\-]{8,}|base64:[A-Za-z0-9+\/=]{20,}|\$2[aby]\$\d{2}\$[.\/A-Za-z0-9]{40,})/', self::MASK, $data) ?? $data;

            return mb_strlen($data) > $max ? mb_substr($data, 0, $max).'… (dipotong)' : $data;
        }

        return $data;
    }
}
