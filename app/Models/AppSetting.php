<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description'];

    protected function casts(): array
    {
        return [];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("app_setting_{$key}", 300, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) return $default;

        return match($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json'    => json_decode($setting->value, true),
            default   => $setting->value,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        $setting = static::where('key', $key)->first();
        if ($setting) {
            $setting->update(['value' => is_array($value) ? json_encode($value) : (string) $value]);
        } else {
            static::create(['key' => $key, 'value' => is_array($value) ? json_encode($value) : (string) $value]);
        }
        Cache::forget("app_setting_{$key}");
    }

    public static function getGroup(string $group): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('group', $group)->orderBy('key')->get();
    }
}
