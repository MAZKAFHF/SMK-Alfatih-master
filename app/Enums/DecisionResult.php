<?php

namespace App\Enums;

enum DecisionResult: string
{
    case Passed = 'passed';
    case NotPassed = 'not_passed';

    public function label(): string
    {
        return match ($this) {
            self::Passed => 'Lulus',
            self::NotPassed => 'Belum Lulus',
        };
    }
}
