<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ExpoGalleryItem extends Model
{
    protected $fillable = [
        'expo_id', 'type', 'url', 'caption', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function expo(): BelongsTo
    {
        return $this->belongsTo(Expo::class);
    }

    /**
     * Whether this item is a video (YouTube, Vimeo, or direct video URL).
     */
    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Returns a resolved public URL for images stored locally,
     * or the raw URL for external images.
     */
    public function resolvedUrl(): string
    {
        if (str_starts_with($this->url, 'http')) {
            return $this->url;
        }

        return Storage::url($this->url);
    }

    /**
     * For video URLs, returns an embeddable iframe src.
     * Handles YouTube (youtu.be / youtube.com) and Vimeo.
     * Falls back to the raw URL for direct video files.
     */
    public function embedUrl(): ?string
    {
        $url = $this->url;

        // YouTube short link: youtu.be/{id}
        if (preg_match('#youtu\.be/([a-zA-Z0-9_\-]+)#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        // YouTube long link: youtube.com/watch?v={id}
        if (preg_match('#youtube\.com/watch\?.*v=([a-zA-Z0-9_\-]+)#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        // Vimeo: vimeo.com/{id}
        if (preg_match('#vimeo\.com/(\d+)#', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        // Direct video file — not embeddable via iframe, return null
        return null;
    }
}
