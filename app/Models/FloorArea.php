<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloorArea extends Model
{
    protected $fillable = [
        'expo_id', 'label', 'type',
        'bg_color', 'text_color',
        'grid_x', 'grid_y', 'grid_w', 'grid_h',
    ];

    // Preset colors for each zone type
    public static array $typePresets = [
        'stage'        => ['bg' => '#1e3a5f', 'text' => '#93c5fd'],
        'tent'         => ['bg' => '#3b1a45', 'text' => '#e9d5ff'],
        'entrance'     => ['bg' => '#14532d', 'text' => '#86efac'],
        'registration' => ['bg' => '#713f12', 'text' => '#fde68a'],
        'parking'      => ['bg' => '#374151', 'text' => '#d1d5db'],
        'vip'          => ['bg' => '#7f1d1d', 'text' => '#fca5a5'],
        'other'        => ['bg' => '#292524', 'text' => '#d6d3d1'],
    ];

    public function expo(): BelongsTo
    {
        return $this->belongsTo(Expo::class);
    }
}
