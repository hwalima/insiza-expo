<?php

namespace App\Models;

use App\Enums\StandCategory;
use App\Enums\StandSize;
use App\Enums\StandStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stand extends Model
{
    protected $fillable = [
        'expo_id', 'stand_number', 'size', 'category', 'status', 'price',
        'grid_x', 'grid_y', 'grid_w', 'grid_h', 'rotation',
        'section', 'exhibitor_name', 'exhibitor_logo', 'is_placed',
    ];

    protected $casts = [
        'size'      => StandSize::class,
        'status'    => StandStatus::class,
        'category'  => StandCategory::class,
        'price'     => 'decimal:2',
        'is_placed' => 'boolean',
    ];

    public function expo(): BelongsTo
    {
        return $this->belongsTo(Expo::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBooking(): ?Booking
    {
        return $this->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->latest()
            ->first();
    }
}
