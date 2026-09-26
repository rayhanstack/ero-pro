<?php

namespace App\Models;

use App\Enums\ClientStatusEnum;
use App\Helpers\MediaHelper;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'website',
        'logo',
        'country_id',
        'state_id',
        'city_id',
        'address',
        'industry',
        'currency_id',
        'status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ClientStatusEnum::class,
            'logo' => 'array',
        ];
    }

    /**
     * Generate the next client code.
     */
    public static function generateCode(): string
    {
        $lastId = static::withTrashed()->max('id') ?? 0;

        return sprintf('CLT-%04d', $lastId + 1);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (empty($client->code)) {
                $client->code = static::generateCode();
            }
        });
    }

    /**
     * Client avatar / logo URL accessor.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return MediaHelper::url($this->logo);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->company_name) . '&background=4f46e5&color=fff';
    }

    /**
     * Client contacts relationship.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    /**
     * Client notes relationship.
     */
    public function clientNotes(): HasMany
    {
        return $this->hasMany(ClientNote::class)->latest();
    }

    /**
     * Country relationship.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * State relationship.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * City relationship.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Scope query to active clients.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ClientStatusEnum::ACTIVE);
    }

    /**
     * Scope query by search term.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('company_name', 'like', "%{$search}%")
                ->orWhere('contact_name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('industry', 'like', "%{$search}%");
        });
    }

    /**
     * Scope query by status.
     */
    public function scopeStatus(Builder $query, $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }
}
