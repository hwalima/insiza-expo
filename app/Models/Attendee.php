<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendee extends Model
{
    protected $fillable = [
        'expo_id', 'name', 'organisation', 'email', 'phone',
        'registration_number', 'checked_in', 'checked_in_at',
    ];

    protected $casts = [
        'checked_in'    => 'boolean',
        'checked_in_at' => 'datetime',
    ];

    public function expo(): BelongsTo
    {
        return $this->belongsTo(Expo::class);
    }

    public static function generateRegNumber(int $expoYear): string
    {
        $prefix = "IDI{$expoYear}";
        $last   = static::where('registration_number', 'like', "{$prefix}-%")
                        ->orderByDesc('id')->first();
        $seq = $last ? ((int) substr($last->registration_number, -4)) + 1 : 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function verifyUrl(): string
    {
        return url('/verify/' . $this->registration_number);
    }

    public function qrCodeUrl(int $size = 220): string
    {
        // Black on white — maximum scanner contrast
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size
             . '&data=' . urlencode($this->verifyUrl())
             . '&color=000000&bgcolor=ffffff&margin=6';
    }
}
