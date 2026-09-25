<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Weekend extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'day_of_week',
        'name',
        'is_weekend',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_weekend' => 'boolean',
        ];
    }

    /**
     * Model boot hooks.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('calendar.weekends');
        });

        static::deleted(function () {
            Cache::forget('calendar.weekends');
        });
    }
}
