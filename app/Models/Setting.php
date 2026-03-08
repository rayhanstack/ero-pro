<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
     protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            Cache::forget('settings.base');
        });

        static::deleted(function (Setting $setting) {
            Cache::forget('settings.base');
        });
    }
}
