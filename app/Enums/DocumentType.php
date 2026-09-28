<?php

namespace App\Enums;

enum DocumentType: string
{
    case Kk = 'kk';
    case KtpOrtu = 'ktp_ortu';
    case Akta = 'akta';
    case Rapor = 'rapor';
    case Foto = 'foto';
    case Ijazah = 'ijazah';

    public function label(): string
    {
        return match ($this) {
            self::Kk => 'Kartu Keluarga (KK)',
            self::KtpOrtu => 'KTP Orang Tua / Wali',
            self::Akta => 'Akta Kelahiran',
            self::Rapor => 'Rapor',
            self::Foto => 'Foto Siswa',
            self::Ijazah => 'Ijazah (tahap lanjut)',
        };
    }

    public function accept(): string
    {
        return $this === self::Foto ? 'image/jpeg,image/png,image/webp' : 'image/jpeg,image/png,image/webp,application/pdf';
    }

    public function maxKb(): int
    {
        return $this === self::Foto ? 2048 : 4096;
    }

    /** @return DocumentType[] */
    public static function requiredInitially(): array
    {
        return [self::Kk, self::KtpOrtu, self::Akta, self::Rapor, self::Foto];
    }
}
