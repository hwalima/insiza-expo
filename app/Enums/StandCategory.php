<?php

namespace App\Enums;

enum StandCategory: string
{
    case Mining        = 'mining';
    case Agriculture   = 'agriculture';
    case Education     = 'education';
    case Organisations = 'organisations';
    case General       = 'general';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match($this) {
            self::Mining        => '#ECCE8E',
            self::Agriculture   => '#4CB944',
            self::Education     => '#8D91C7',
            self::Organisations => '#AFECE7',
            self::General       => '#FFB7C3',
        };
    }

    /** Dark text on light backgrounds, white on dark ones */
    public function textColor(): string
    {
        return match($this) {
            self::Mining, self::Organisations, self::General => '#111D02',
            default => '#ffffff',
        };
    }
}
