<?php

namespace App\Models;

use App\Enums\EmployeeStatusEnum;
use App\Enums\EmploymentTypeEnum;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'status',
        'last_login_at',
        'time_zone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => EmployeeStatusEnum::class,
        ];
    }

    /**
     * Get user avatar URL with default fallback.
     */
    public function getAvatarUrlAttribute(): string
    {
        if (! empty($this->avatar)) {
            return getFilePath($this->avatar, 'avatar');
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?: 'User') . '&background=4f46e5&color=fff';
    }

    /**
     * Alias for full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * First name parsed from name.
     */
    public function getFirstNameAttribute(): string
    {
        $parts = explode(' ', trim($this->name), 2);

        return $parts[0] ?? '';
    }

    /**
     * Last name parsed from name.
     */
    public function getLastNameAttribute(): string
    {
        $parts = explode(' ', trim($this->name), 2);

        return $parts[1] ?? '';
    }

    /**
     * Proxy for employee code.
     */
    public function getEmpCodeAttribute(): string
    {
        return $this->employeeDetail?->emp_code ?? 'EMP-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Safe status enum accessor.
     */
    public function getStatusEnumAttribute(): EmployeeStatusEnum
    {
        if ($this->status instanceof EmployeeStatusEnum) {
            return $this->status;
        }

        return EmployeeStatusEnum::tryFrom((string) $this->status) ?? EmployeeStatusEnum::ACTIVE;
    }

    /**
     * Delegated department accessor.
     */
    public function getDepartmentAttribute(): ?Department
    {
        return $this->employeeDetail?->department;
    }

    /**
     * Delegated designation accessor.
     */
    public function getDesignationAttribute(): ?Designation
    {
        return $this->employeeDetail?->designation;
    }

    /**
     * Delegated shift accessor.
     */
    public function getShiftAttribute(): ?Shift
    {
        return $this->employeeDetail?->shift;
    }

    /**
     * Delegated manager accessor.
     */
    public function getManagerAttribute(): ?User
    {
        return $this->employeeDetail?->manager;
    }

    /**
     * Delegated employment type accessor.
     */
    public function getEmploymentTypeAttribute(): ?EmploymentTypeEnum
    {
        return $this->employeeDetail?->employment_type;
    }

    /**
     * Delegated joining date accessor.
     */
    public function getJoiningDateAttribute()
    {
        return $this->employeeDetail?->joining_date;
    }

    /**
     * Delegated basic salary accessor.
     */
    public function getBasicSalaryAttribute()
    {
        return $this->employeeDetail?->basic_salary ?? 0.00;
    }

    /**
     * Check if user account is active.
     */
    public function isActive(): bool
    {
        return $this->status === EmployeeStatusEnum::ACTIVE;
    }

    /**
     * Get user role name.
     */
    public function getRoleNameAttribute(): string
    {
        return $this->roles->first()?->name ?? _trans('common.Employee');
    }

    /**
     * Employee specific metadata relation.
     */
    public function employeeDetail(): HasOne
    {
        return $this->hasOne(EmployeeDetail::class, 'user_id');
    }

    /**
     * Alias for employeeDetail relation.
     */
    public function detail(): HasOne
    {
        return $this->employeeDetail();
    }

    /**
     * Documents uploaded for this employee.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'user_id');
    }

    /**
     * Emergency contacts for this employee.
     */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeEmergencyContact::class, 'user_id');
    }

    /**
     * Bank accounts for this employee.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(EmployeeBankAccount::class, 'user_id');
    }

    /**
     * Primary bank account.
     */
    public function primaryBankAccount(): HasOne
    {
        return $this->hasOne(EmployeeBankAccount::class, 'user_id')->where('is_primary', true);
    }

    /**
     * Attendance records for this employee.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    /**
     * Attendance regularization requests for this employee.
     */
    public function attendanceRegularizations(): HasMany
    {
        return $this->hasMany(AttendanceRegularization::class, 'employee_id');
    }

    /**
     * Leave balances for this employee.
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'employee_id');
    }

    /**
     * Leave requests for this employee.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    /**
     * Teams where this user is a member.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members', 'user_id', 'team_id')
            ->withTimestamps();
    }

    /**
     * Teams where this user is the team lead.
     */
    public function ledTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'lead_id');
    }

    /**
     * Projects where this user is an assigned member.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members', 'employee_id', 'project_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Projects managed by this user.
     */
    public function managedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    /**
     * Tasks assigned to this user.
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignees', 'employee_id', 'task_id')
            ->withTimestamps();
    }

    /**
     * Tasks created by this user.
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Direct reports / Subordinates.
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(EmployeeDetail::class, 'manager_id');
    }

    /**
     * Salary components assigned to this employee.
     */
    public function employeeSalaryComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class, 'employee_id');
    }

    /**
     * Payslips generated for this employee.
     */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'employee_id');
    }

    /**
     * Scope query to active users/employees.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EmployeeStatusEnum::ACTIVE->value);
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
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhereHas('employeeDetail', function ($dq) use ($search) {
                    $dq->where('emp_code', 'like', "%{$search}%")
                        ->orWhere('nid', 'like', "%{$search}%");
                });
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

        return $query->whereHas('employeeDetail', function ($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }

    /**
     * Scope query to filter by designation.
     */
    public function scopeFilterByDesignation(Builder $query, $designationId): Builder
    {
        if (empty($designationId)) {
            return $query;
        }

        return $query->whereHas('employeeDetail', function ($q) use ($designationId) {
            $q->where('designation_id', $designationId);
        });
    }

    /**
     * Scope query to filter by status.
     */
    public function scopeFilterByStatus(Builder $query, $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        $val = $status instanceof EmployeeStatusEnum ? $status->value : $status;

        return $query->where('status', $val);
    }

    /**
     * Scope query to filter by role.
     */
    public function scopeFilterByRole(Builder $query, $role): Builder
    {
        if (empty($role)) {
            return $query;
        }

        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }
}
