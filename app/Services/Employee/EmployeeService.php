<?php

namespace App\Services\Employee;

use App\Enums\EmployeeStatusEnum;
use App\Helpers\MediaHelper;
use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmergencyContact;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmployeeService
{
    /**
     * Get paginated employees with eager-loaded relations and search filters.
     */
    public function getPaginatedEmployees(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Employee::with([
            'department',
            'designation',
            'shift',
            'manager',
            'user',
            'primaryBankAccount',
        ])->latest('id');

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['department_id'])) {
            $query->filterByDepartment($filters['department_id']);
        }

        if (! empty($filters['designation_id'])) {
            $query->filterByDesignation($filters['designation_id']);
        }

        if (! empty($filters['status'])) {
            $query->filterByStatus($filters['status']);
        }

        if (! empty($filters['employment_type'])) {
            $query->filterByEmploymentType($filters['employment_type']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get summary KPI statistics for employee dashboard.
     */
    public function getEmployeeStats(): array
    {
        $now = Carbon::now();

        return [
            'total' => Employee::count(),
            'active' => Employee::where('status', EmployeeStatusEnum::ACTIVE)->count(),
            'on_leave' => Employee::where('status', EmployeeStatusEnum::ON_LEAVE)->count(),
            'new_this_month' => Employee::whereMonth('joining_date', $now->month)
                ->whereYear('joining_date', $now->year)
                ->count(),
        ];
    }

    /**
     * Create a new employee with related bank, emergency contact, document, and user account.
     */
    public function create(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            // Handle Avatar Upload
            $avatarPayload = null;
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $avatarPayload = MediaHelper::upload($data['avatar'], 'employees/avatars');
            }

            // Handle optional User Account creation
            $userId = null;
            if (! empty($data['create_user_account']) && ! empty($data['role'])) {
                $rawPassword = $data['user_password'] ?? Str::password(10);
                $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

                $user = User::create([
                    'name' => $fullName,
                    'email' => $data['email'],
                    'password' => Hash::make($rawPassword),
                    'phone' => $data['phone'] ?? null,
                    'status' => 'active',
                    'time_zone' => config('app.timezone', 'UTC'),
                    'avatar' => $avatarPayload ? json_encode($avatarPayload) : null,
                    'email_verified_at' => now(),
                ]);

                $user->syncRoles([$data['role']]);
                $userId = $user->id;

                // Log the generated credentials
                Log::info("Employee user account created for {$data['email']} with role {$data['role']} and initial password: {$rawPassword}");
            }

            // Create Employee Record
            $employeeData = [
                'user_id' => $userId,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'dob' => $data['dob'] ?? null,
                'gender' => $data['gender'],
                'marital_status' => $data['marital_status'] ?? null,
                'nid' => $data['nid'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'avatar' => $avatarPayload ? json_encode($avatarPayload) : null,
                'department_id' => $data['department_id'],
                'designation_id' => $data['designation_id'],
                'shift_id' => $data['shift_id'] ?? null,
                'manager_id' => $data['manager_id'] ?? null,
                'joining_date' => $data['joining_date'],
                'confirmation_date' => $data['confirmation_date'] ?? null,
                'employment_type' => $data['employment_type'],
                'status' => $data['status'],
                'basic_salary' => $data['basic_salary'] ?? 0.00,
                'country_id' => $data['country_id'] ?? null,
                'state_id' => $data['state_id'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'present_address' => $data['present_address'] ?? null,
                'permanent_address' => $data['permanent_address'] ?? null,
            ];

            $employee = Employee::create($employeeData);

            // Link User's employee_id if user was created
            if ($userId) {
                User::where('id', $userId)->update(['employee_id' => $employee->id]);
            }

            // Create Primary Bank Account if provided
            if (! empty($data['bank']) && ! empty($data['account_no'])) {
                EmployeeBankAccount::create([
                    'employee_id' => $employee->id,
                    'bank' => $data['bank'],
                    'branch' => $data['branch'] ?? null,
                    'account_name' => $data['account_name'] ?? $employee->full_name,
                    'account_no' => $data['account_no'],
                    'routing_number' => $data['routing_number'] ?? null,
                    'swift_code' => $data['swift_code'] ?? null,
                    'is_primary' => true,
                ]);
            }

            // Create Emergency Contact if provided
            if (! empty($data['emergency_name']) && ! empty($data['emergency_phone'])) {
                EmployeeEmergencyContact::create([
                    'employee_id' => $employee->id,
                    'name' => $data['emergency_name'],
                    'relationship' => $data['emergency_relationship'] ?? 'Family',
                    'phone' => $data['emergency_phone'],
                    'alt_phone' => $data['emergency_alt_phone'] ?? null,
                    'address' => $data['emergency_address'] ?? null,
                ]);
            }

            // Create Initial Document if provided
            if (! empty($data['document_title']) && isset($data['document_file']) && $data['document_file'] instanceof UploadedFile) {
                $docPayload = MediaHelper::upload($data['document_file'], 'employees/documents');
                if ($docPayload) {
                    EmployeeDocument::create([
                        'employee_id' => $employee->id,
                        'title' => $data['document_title'],
                        'file' => json_encode($docPayload),
                        'expiry_date' => $data['document_expiry_date'] ?? null,
                    ]);
                }
            }

            return $employee;
        });
    }

    /**
     * Update existing employee record and relations.
     */
    public function update(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $employeeData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'dob' => $data['dob'] ?? null,
                'gender' => $data['gender'],
                'marital_status' => $data['marital_status'] ?? null,
                'nid' => $data['nid'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'department_id' => $data['department_id'],
                'designation_id' => $data['designation_id'],
                'shift_id' => $data['shift_id'] ?? null,
                'manager_id' => $data['manager_id'] ?? null,
                'joining_date' => $data['joining_date'],
                'confirmation_date' => $data['confirmation_date'] ?? null,
                'employment_type' => $data['employment_type'],
                'status' => $data['status'],
                'basic_salary' => $data['basic_salary'] ?? $employee->basic_salary,
                'country_id' => $data['country_id'] ?? null,
                'state_id' => $data['state_id'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'present_address' => $data['present_address'] ?? null,
                'permanent_address' => $data['permanent_address'] ?? null,
            ];

            // Handle Avatar Replacement
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                if ($employee->avatar) {
                    $old = json_decode($employee->avatar, true);
                    if (isset($old['file'])) {
                        MediaHelper::delete($old['file']);
                    }
                }
                $avatarPayload = MediaHelper::upload($data['avatar'], 'employees/avatars');
                $employeeData['avatar'] = json_encode($avatarPayload);
            }

            $employee->update($employeeData);

            // Sync Primary Bank Account if provided
            if (! empty($data['bank']) && ! empty($data['account_no'])) {
                $primaryBank = $employee->primaryBankAccount;
                if ($primaryBank) {
                    $primaryBank->update([
                        'bank' => $data['bank'],
                        'branch' => $data['branch'] ?? null,
                        'account_name' => $data['account_name'] ?? $employee->full_name,
                        'account_no' => $data['account_no'],
                        'routing_number' => $data['routing_number'] ?? null,
                        'swift_code' => $data['swift_code'] ?? null,
                    ]);
                } else {
                    EmployeeBankAccount::create([
                        'employee_id' => $employee->id,
                        'bank' => $data['bank'],
                        'branch' => $data['branch'] ?? null,
                        'account_name' => $data['account_name'] ?? $employee->full_name,
                        'account_no' => $data['account_no'],
                        'routing_number' => $data['routing_number'] ?? null,
                        'swift_code' => $data['swift_code'] ?? null,
                        'is_primary' => true,
                    ]);
                }
            }

            // Sync Primary Emergency Contact if provided
            if (! empty($data['emergency_name']) && ! empty($data['emergency_phone'])) {
                $contact = $employee->emergencyContacts()->first();
                if ($contact) {
                    $contact->update([
                        'name' => $data['emergency_name'],
                        'relationship' => $data['emergency_relationship'] ?? 'Family',
                        'phone' => $data['emergency_phone'],
                        'alt_phone' => $data['emergency_alt_phone'] ?? null,
                        'address' => $data['emergency_address'] ?? null,
                    ]);
                } else {
                    EmployeeEmergencyContact::create([
                        'employee_id' => $employee->id,
                        'name' => $data['emergency_name'],
                        'relationship' => $data['emergency_relationship'] ?? 'Family',
                        'phone' => $data['emergency_phone'],
                        'alt_phone' => $data['emergency_alt_phone'] ?? null,
                        'address' => $data['emergency_address'] ?? null,
                    ]);
                }
            }

            return $employee;
        });
    }

    /**
     * Change employee status.
     */
    public function changeStatus(Employee $employee, string|EmployeeStatusEnum $status): Employee
    {
        $statusVal = $status instanceof EmployeeStatusEnum ? $status : EmployeeStatusEnum::from($status);
        $employee->status = $statusVal;
        $employee->save();

        return $employee;
    }

    /**
     * Soft delete an employee.
     */
    public function delete(Employee $employee): bool
    {
        return (bool) $employee->delete();
    }

    /**
     * Restore a soft-deleted employee.
     */
    public function restore(int $employeeId): ?Employee
    {
        $employee = Employee::withTrashed()->find($employeeId);
        if ($employee) {
            $employee->restore();
        }

        return $employee;
    }

    /**
     * Add a document to an employee.
     */
    public function addDocument(Employee $employee, array $data): EmployeeDocument
    {
        $docPayload = MediaHelper::upload($data['file'], 'employees/documents');

        return EmployeeDocument::create([
            'employee_id' => $employee->id,
            'title' => $data['title'],
            'file' => json_encode($docPayload),
            'expiry_date' => $data['expiry_date'] ?? null,
        ]);
    }

    /**
     * Delete an employee document.
     */
    public function deleteDocument(EmployeeDocument $document): bool
    {
        if ($document->file) {
            $data = json_decode($document->file, true);
            if (isset($data['file'])) {
                MediaHelper::delete($data['file']);
            } elseif (is_string($document->file)) {
                MediaHelper::delete($document->file);
            }
        }

        return (bool) $document->delete();
    }
}
