<?php

namespace App\Enums;

enum InterviewAppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Attended = 'attended';
    case NoShow = 'no_show';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Terjadwal',
            self::Attended => 'Hadir',
            self::NoShow => 'Tidak Hadir',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Scheduled => 'sky',
            self::Attended => 'green',
            self::NoShow => 'red',
            self::Cancelled => 'slate',
        };
    }
}
