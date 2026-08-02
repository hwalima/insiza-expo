<?php

namespace App\Enums;

enum StandSize: string
{
    case Large = '6x3';
    case Small = '3x3';

    public function label(): string
    {
        return match($this) {
            StandSize::Large => '6×3 m (Large)',
            StandSize::Small => '3×3 m (Small)',
        };
    }
}
