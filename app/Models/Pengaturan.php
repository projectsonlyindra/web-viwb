<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $primaryKey = 'kunci';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'kunci',
        'nilai',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $setting = static::query()->find($key);
        } catch (\Throwable) {
            return $default;
        }

        if (! $setting) {
            return $default;
        }

        return match ($setting->nilai) {
            'true', '1' => true,
            'false', '0' => false,
            default => $setting->nilai,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        $storedValue = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
        static::query()->updateOrCreate(['kunci' => $key], ['nilai' => $storedValue]);
    }
}
