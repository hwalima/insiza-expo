<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Expo extends Model
{
    protected $fillable = [
        'name', 'year', 'start_date', 'end_date', 'venue',
        'description', 'theme', 'previous_winner',
        'previous_winner_category', 'previous_winner_logo', 'previous_winner_image',
        'is_active', 'contact_phone', 'contact_email',
        'floor_plan_image', 'is_layout_published',
    ];

    protected $casts = [
        'start_date'           => 'date',
        'end_date'             => 'date',
        'is_active'            => 'boolean',
        'is_layout_published'  => 'boolean',
    ];

    public static function active(): ?self
    {
        return self::where('is_active', true)->latest()->first();
    }

    public function guestOfHonor(): HasOne
    {
        return $this->hasOne(GuestOfHonor::class);
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(Sponsor::class)->orderBy('sort_order');
    }

    public function stands(): HasMany
    {
        return $this->hasMany(Stand::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
