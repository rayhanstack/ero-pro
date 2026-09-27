<?php

namespace App\Models;

use App\Enums\MilestoneStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'due_date',
        'cost',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'cost' => 'decimal:2',
            'status' => MilestoneStatusEnum::class,
        ];
    }

    /**
     * Project relationship.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
