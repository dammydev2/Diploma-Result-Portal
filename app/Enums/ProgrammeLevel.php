<?php

namespace App\Enums;

enum ProgrammeLevel: string
{
    case NationalDiploma = 'ND';
    case HigherNationalDiploma = 'HND';

    public function label(): string
    {
        return match ($this) {
            self::NationalDiploma => 'National Diploma',
            self::HigherNationalDiploma => 'Higher National Diploma',
        };
    }
}
