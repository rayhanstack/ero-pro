<?php

namespace App\Models;

use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectStatusEnum;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'client_id',
        'description',
        'start_date',
        'deadline',
        'budget',
        'currency_id',
        'priority',
        'status',
        'progress',
        'manager_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'deadline' => 'date',
            'budget' => 'decimal:2',
            'progress' => 'integer',
            'priority' => ProjectPriorityEnum::class,
            'status' => ProjectStatusEnum::class,
        ];
    }

    /**
     * Generate the next unique project code (PRJ-0001).
     */
    public static function generateCode(): string
    {
        $lastId = static::withTrashed()->max('id') ?? 0;

        return sprintf('PRJ-%04d', $lastId + 1);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->code)) {
                $project->code = static::generateCode();
            }
        });
    }

    /**
     * Client relationship.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Project Manager relationship (User/Employee).
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Project members relationship (Users/Employees).
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members', 'project_id', 'employee_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Project member pivot model instances.
     */
    public function projectMembers(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * Project milestones relationship.
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('due_date');
    }

    /**
     * Project files relationship.
     */
    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class)->latest();
    }

    /**
     * Formatted budget with currency symbol.
     */
    public function getFormattedBudgetAttribute(): string
    {
        $symbol = $this->currency?->symbol ?? '$';

        return $symbol . number_format((float) $this->budget, 2);
    }

    /**
     * Scope query by search term (name, code, client name, description).
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('client', function (Builder $cq) use ($search) {
                    $cq->where('company_name', 'like', "%{$search}%");
                });
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

    /**
     * Scope query by client.
     */
    public function scopeClient(Builder $query, $clientId): Builder
    {
        if (empty($clientId)) {
            return $query;
        }

        return $query->where('client_id', $clientId);
    }

    /**
     * Scope query by priority.
     */
    public function scopePriority(Builder $query, $priority): Builder
    {
        if (empty($priority)) {
            return $query;
        }

        return $query->where('priority', $priority);
    }

    /**
     * Scope query by manager.
     */
    public function scopeManager(Builder $query, $managerId): Builder
    {
        if (empty($managerId)) {
            return $query;
        }

        return $query->where('manager_id', $managerId);
    }
}
