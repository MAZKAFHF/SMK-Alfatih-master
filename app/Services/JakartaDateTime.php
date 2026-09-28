<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/**
 * Jalur kanonik input waktu lokal sekolah.
 * Database proyek memakai kolom datetime tanpa offset dan APP_TIMEZONE Jakarta,
 * sehingga wall-clock Jakarta disimpan apa adanya (bukan digeser ke UTC).
 */
final class JakartaDateTime
{
    public const TIMEZONE = 'Asia/Jakarta';

    public static function toStorage(?string $value, string $field): ?CarbonImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            $raw = trim($value);
            $local = false;
            foreach (['!Y-m-d\TH:i', '!Y-m-d\TH:i:s'] as $format) {
                try {
                    $candidate = CarbonImmutable::createFromFormat($format, $raw, self::TIMEZONE);
                } catch (\Throwable) {
                    continue;
                }
                $errors = CarbonImmutable::getLastErrors();
                if ($candidate !== false && (! is_array($errors) || ($errors['warning_count'] === 0 && $errors['error_count'] === 0)) && $candidate->format(substr($format, 1)) === $raw) {
                    $local = $candidate;
                    break;
                }
            }
            if ($local === false) {
                throw new \InvalidArgumentException();
            }
        } catch (\Throwable) {
            throw ValidationException::withMessages([$field => 'Tanggal dan jam yang dimasukkan tidak valid. Gunakan format DD-MM-YYYY dan HH:mm.']);
        }

        return $local;
    }

    public static function forInput(mixed $value): string
    {
        if (! $value) {
            return '';
        }

        return CarbonImmutable::parse($value)->timezone(self::TIMEZONE)->format('Y-m-d\TH:i');
    }
}
