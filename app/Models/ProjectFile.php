<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectFile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'user_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
    ];

    /**
     * Project relationship.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * User / Uploader relationship.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get accessible download / view URL.
     */
    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return asset('storage/' . ltrim($this->file_path, '/'));
    }

    /**
     * Icon representation based on extension.
     */
    public function getIconAttribute(): string
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'bi-file-earmark-pdf text-danger',
            'doc', 'docx' => 'bi-file-earmark-word text-primary',
            'xls', 'xlsx', 'csv' => 'bi-file-earmark-excel text-success',
            'zip', 'rar', '7z', 'tar', 'gz' => 'bi-file-earmark-zip text-warning',
            'jpg', 'jpeg', 'png', 'webp', 'svg' => 'bi-file-earmark-image text-info',
            'ppt', 'pptx' => 'bi-file-earmark-ppt text-danger',
            default => 'bi-file-earmark-text text-secondary',
        };
    }
}
