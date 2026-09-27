<?php

namespace App\Models;

use App\Enums\BloodGroupEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\MaritalStatusEnum;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDetail extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'employee_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'emp_code',
        'dob',
        'gender',
        'marital_status',
        'nid',
        'blood_group',
        'department_id',
        'designation_id',
        'shift_id',
        'manager_id',
        'joining_date',
        'confirmation_date',
        'employment_type',
        'basic_salary',
        'country_id',
        'state_id',
        'city_id',
        'present_address',
        'permanent_address',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'joining_date' => 'date',
            'confirmation_date' => 'date',
            'gender' => GenderEnum::class,
            'marital_status' => MaritalStatusEnum::class,
            'blood_group' => BloodGroupEnum::class,
            'employment_type' => EmploymentTypeEnum::class,
            'basic_salary' => 'decimal:2',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (EmployeeDetail $detail) {
            if (empty($detail->emp_code)) {
                $latest = static::withTrashed()->latest('id')->first();
                $nextId = $latest ? $latest->id + 1 : 1;
                $detail->emp_code = 'EMP-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * User relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Department relation.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Designation relation.
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Shift relation.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Reporting manager relation (pointing to User).
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Country relation.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * State relation.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * City relation.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
