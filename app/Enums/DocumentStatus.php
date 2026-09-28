<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case NotUploaded = 'not_uploaded';
    case Uploaded = 'uploaded';
    case UnderReview = 'under_review';
    case Valid = 'valid';
    case NeedsRevision = 'needs_revision';
    case Replaced = 'replaced';

    public function label(): string
    {
        return match ($this) {
            self::NotUploaded => 'Belum Diunggah',
            self::Uploaded => 'Terunggah',
            self::UnderReview => 'Diperiksa',
            self::Valid => 'Valid',
            self::NeedsRevision => 'Perlu Diperbaiki',
            self::Replaced => 'Diperbarui',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::NotUploaded => 'slate',
            self::Uploaded => 'sky',
            self::UnderReview => 'amber',
            self::Valid => 'green',
            self::NeedsRevision => 'red',
            self::Replaced => 'violet',
        };
    }
}
