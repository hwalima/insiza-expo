<?php

namespace App\Enums;

enum SponsorTier: string
{
    case Gold    = 'gold';
    case Silver  = 'silver';
    case Bronze  = 'bronze';
    case Partner = 'partner';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match($this) {
            SponsorTier::Gold    => '#D29500',
            SponsorTier::Silver  => '#9ca3af',
            SponsorTier::Bronze  => '#b45309',
            SponsorTier::Partner => '#185909',
        };
    }
}
