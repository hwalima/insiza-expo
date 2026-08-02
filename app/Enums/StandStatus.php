<?php

namespace App\Enums;

enum StandStatus: string
{
    case Available = 'available';
    case Reserved  = 'reserved';
    case Occupied  = 'occupied';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match($this) {
            StandStatus::Available => '#22c55e', // green
            StandStatus::Reserved  => '#f59e0b', // amber
            StandStatus::Occupied  => '#ef4444', // red
        };
    }
}
