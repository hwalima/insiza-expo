<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    // Cache TTL in seconds
    private const CACHE_TTL = 3600;

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", self::CACHE_TTL, function () use ($key, $default) {
            $s = static::where('key', $key)->first();
            if (!$s) return $default;
            if ($s->type === 'password' && $s->value) {
                try { return decrypt($s->value); } catch (\Throwable) { return $s->value; }
            }
            if ($s->type === 'boolean') return filter_var($s->value, FILTER_VALIDATE_BOOLEAN);
            return $s->value;
        });
    }

    public static function set(string $key, mixed $value, string $type = 'text', string $group = 'general'): void
    {
        Cache::forget("setting.{$key}");
        $stored = ($type === 'password' && $value) ? encrypt($value) : $value;
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'type' => $type, 'group' => $group]
        );
    }

    public static function getGroup(string $group): array
    {
        return static::where('group', $group)->get()
            ->mapWithKeys(function ($s) {
                $val = $s->value;
                if ($s->type === 'password' && $val) {
                    try { $val = decrypt($val); } catch (\Throwable) {}
                }
                if ($s->type === 'boolean') $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                return [$s->key => $val];
            })->all();
    }
}
