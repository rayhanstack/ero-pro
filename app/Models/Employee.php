<?php

namespace App\Models;

use App\Enums\BloodGroupEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\MaritalStatusEnum;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'emp_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'dob',
        'gender',
        'marital_status',
        'nid',
        'blood_group',
        'avatar',
        'department_id',
        'designation_id',
        'shift_id',
        'manager_id',
        'joining_date',
        'confirmation_date',
        'employment_type',
        'status',
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
            'status' => EmployeeStatusEnum::class,
            'basic_salary' => 'decimal:2',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Employee $employee) {
            if (empty($employee->emp_code)) {
                $latest = static::withTrashed()->latest('id')->first();
                $nextId = $latest ? $latest->id + 1 : 1;
                $employee->emp_code = 'EMP-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Alias for full name.
     */
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Get the employee's avatar url with fallback.
     */
    public function getAvatarUrlAttribute(): string
    {
        $default = 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name ?: 'Employee') . '&background=4f46e5&color=fff';

        return getFilePath($this->avatar, $default);
    }

    /**
     * Related user account.
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
     * Manager (Reporting to) relation.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Subordinates / Direct reports.
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
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

    /**
     * Employee uploaded documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /**
     * Employee emergency contacts.
     */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    /**
     * Employee bank accounts.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(EmployeeBankAccount::class);
    }

    /**
     * Primary bank account.
     */
    public function primaryBankAccount(): HasOne
    {
        return $this->hasOne(EmployeeBankAccount::class)->where('is_primary', true);
    }

    /**
     * Scope query to active employees.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EmployeeStatusEnum::ACTIVE);
    }

    /**
     * Scope query to search across names, employee code, email, phone, and NID.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('emp_code', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('nid', 'like', "%{$search}%");
        });
    }

    /**
     * Scope query to filter by department.
     */
    public function scopeFilterByDepartment(Builder $query, $departmentId): Builder
    {
        if (empty($departmentId)) {
            return $query;
        }

        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope query to filter by designation.
     */
    public function scopeFilterByDesignation(Builder $query, $designationId): Builder
    {
        if (empty($designationId)) {
            return $query;
        }

        return $query->where('designation_id', $designationId);
    }

    /**
     * Scope query to filter by status.
     */
    public function scopeFilterByStatus(Builder $query, $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Scope query to filter by employment type.
     */
    public function scopeFilterByEmploymentType(Builder $query, $type): Builder
    {
        if (empty($type)) {
            return $query;
        }

        return $query->where('employment_type', $type);
    }
}
