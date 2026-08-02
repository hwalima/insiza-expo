<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending   = 'pending';
    case Approved  = 'approved';
    case Rejected  = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badgeClass(): string
    {
        return match($this) {
            BookingStatus::Pending   => 'bg-yellow-500',
            BookingStatus::Approved  => 'bg-green-600',
            BookingStatus::Rejected  => 'bg-red-600',
            BookingStatus::Cancelled => 'bg-gray-500',
        };
    }
}
