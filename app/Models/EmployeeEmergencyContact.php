<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEmergencyContact extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'relationship',
        'phone',
        'alt_phone',
        'address',
    ];

    /**
     * User (Employee) relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
