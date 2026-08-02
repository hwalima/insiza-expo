<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestOfHonor extends Model
{
    protected $fillable = [
        'expo_id', 'name', 'title', 'bio', 'photo', 'organisation',
    ];

    public function expo(): BelongsTo
    {
        return $this->belongsTo(Expo::class);
    }
}
