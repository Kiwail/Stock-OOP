<?php

namespace App\Enums;

enum DocumentType: int
{
    case Income = 1;
    case Writeoff = 2;
    case Transfer = 3;
    case Sale = 4;

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Saņemšana',
            self::Writeoff => 'Norakstīšana',
            self::Transfer => 'Pārvietošana',
            self::Sale => 'Realizācija',
        };
    }
}
