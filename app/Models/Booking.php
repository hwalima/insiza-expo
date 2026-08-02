<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\StandCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'stand_id', 'user_id', 'expo_id',
        'company_name', 'contact_person', 'contact_email', 'contact_phone',
        'category', 'description', 'status', 'payment_verified',
        'payment_reference', 'admin_notes', 'source', 'approved_at',
    ];

    protected $casts = [
        'status'           => BookingStatus::class,
        'category'         => StandCategory::class,
        'payment_verified' => 'boolean',
        'approved_at'      => 'datetime',
    ];

    public function stand(): BelongsTo
    {
        return $this->belongsTo(Stand::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expo(): BelongsTo
    {
        return $this->belongsTo(Expo::class);
    }
}
