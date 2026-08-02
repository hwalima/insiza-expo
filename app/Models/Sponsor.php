<?php

namespace App\Models;

use App\Enums\SponsorTier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sponsor extends Model
{
    protected $fillable = [
        'expo_id', 'name', 'logo', 'website', 'tier', 'sort_order',
    ];

    protected $casts = [
        'tier' => SponsorTier::class,
    ];

    public function expo(): BelongsTo
    {
        return $this->belongsTo(Expo::class);
    }
}
