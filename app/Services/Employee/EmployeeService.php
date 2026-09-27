<?php

namespace App\Services\Employee;

use App\Enums\EmployeeStatusEnum;
use App\Helpers\MediaHelper;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeDetail;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmergencyContact;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    /**
     * Get paginated employees (users) with eager-loaded relations and search filters.
     */
    public function getPaginatedEmployees(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = User::with([
            'detail.department',
            'detail.designation',
            'detail.shift',
            'detail.manager',
            'roles',
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

        if (! empty($filters['role'])) {
            $query->filterByRole($filters['role']);
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
            'total' => User::count(),
            'active' => User::where('status', EmployeeStatusEnum::ACTIVE->value)->count(),
            'on_leave' => User::where('status', EmployeeStatusEnum::ON_LEAVE->value)->count(),
            'new_this_month' => User::whereHas('detail', function ($q) use ($now) {
                $q->whereMonth('joining_date', $now->month)
                    ->whereYear('joining_date', $now->year);
            })->count(),
        ];
    }

    /**
     * Create a new employee (User + EmployeeDetail) with bank, emergency contact, document, and role in one transaction.
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Handle Avatar Upload
            $avatarPayload = null;
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $avatarPayload = MediaHelper::upload($data['avatar'], 'employees/avatars');
            }

            $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            $statusVal = isset($data['status'])
                ? ($data['status'] instanceof EmployeeStatusEnum ? $data['status']->value : $data['status'])
                : EmployeeStatusEnum::ACTIVE->value;

            // 1. Create User
            $user = User::create([
                'name' => $fullName,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'status' => $statusVal,
                'time_zone' => $data['time_zone'] ?? config('app.timezone', 'UTC'),
                'avatar' => $avatarPayload ? json_encode($avatarPayload) : null,
                'email_verified_at' => now(),
            ]);

            // Sync Spatie Role
            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            $genderVal = isset($data['gender'])
                ? ($data['gender'] instanceof \App\Enums\GenderEnum ? $data['gender']->value : $data['gender'])
                : 'male';

            $employmentTypeVal = isset($data['employment_type'])
                ? ($data['employment_type'] instanceof \App\Enums\EmploymentTypeEnum ? $data['employment_type']->value : $data['employment_type'])
                : 'full_time';

            // 2. Create Employee Detail
            $detailData = [
                'user_id' => $user->id,
                'emp_code' => $data['emp_code'] ?? null,
                'dob' => $data['dob'] ?? null,
                'gender' => $genderVal,
                'marital_status' => $data['marital_status'] ?? null,
                'nid' => $data['nid'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'designation_id' => $data['designation_id'] ?? null,
                'shift_id' => $data['shift_id'] ?? null,
                'manager_id' => $data['manager_id'] ?? null,
                'joining_date' => $data['joining_date'] ?? date('Y-m-d'),
                'confirmation_date' => $data['confirmation_date'] ?? null,
                'employment_type' => $employmentTypeVal,
                'basic_salary' => $data['basic_salary'] ?? 0.00,
                'country_id' => $data['country_id'] ?? null,
                'state_id' => $data['state_id'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'present_address' => $data['present_address'] ?? null,
                'permanent_address' => $data['permanent_address'] ?? null,
            ];

            EmployeeDetail::create($detailData);

            // 3. Create Primary Bank Account if provided
            if (! empty($data['bank']) && ! empty($data['account_no'])) {
                EmployeeBankAccount::create([
                    'user_id' => $user->id,
                    'bank' => $data['bank'],
                    'branch' => $data['branch'] ?? null,
                    'account_name' => $data['account_name'] ?? $user->name,
                    'account_no' => $data['account_no'],
                    'routing_number' => $data['routing_number'] ?? null,
                    'swift_code' => $data['swift_code'] ?? null,
                    'is_primary' => true,
                ]);
            }

            // 4. Create Emergency Contact if provided
            if (! empty($data['emergency_name']) && ! empty($data['emergency_phone'])) {
                EmployeeEmergencyContact::create([
                    'user_id' => $user->id,
                    'name' => $data['emergency_name'],
                    'relationship' => $data['emergency_relationship'] ?? 'Family',
                    'phone' => $data['emergency_phone'],
                    'alt_phone' => $data['emergency_alt_phone'] ?? null,
                    'address' => $data['emergency_address'] ?? null,
                ]);
            }

            // 5. Create Initial Document if provided
            if (! empty($data['document_title']) && isset($data['document_file']) && $data['document_file'] instanceof UploadedFile) {
                $docPayload = MediaHelper::upload($data['document_file'], 'employees/documents');
                if ($docPayload) {
                    EmployeeDocument::create([
                        'user_id' => $user->id,
                        'title' => $data['document_title'],
                        'file' => json_encode($docPayload),
                        'expiry_date' => $data['document_expiry_date'] ?? null,
                    ]);
                }
            }

            return $user->load(['detail', 'roles', 'primaryBankAccount']);
        });
    }

    /**
     * Update existing employee (User + EmployeeDetail) and related records.
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            if (empty($fullName)) {
                $fullName = $user->name;
            }
            $statusVal = isset($data['status'])
                ? ($data['status'] instanceof EmployeeStatusEnum ? $data['status']->value : $data['status'])
                : ($user->status instanceof EmployeeStatusEnum ? $user->status->value : $user->status);

            $userData = [
                'name' => $fullName,
                'email' => $data['email'] ?? $user->email,
                'phone' => array_key_exists('phone', $data) ? $data['phone'] : $user->phone,
                'status' => $statusVal,
                'time_zone' => $data['time_zone'] ?? $user->time_zone ?? config('app.timezone', 'UTC'),
            ];

            if (! empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            // Handle Avatar Replacement
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                if ($user->avatar) {
                    $old = json_decode($user->avatar, true);
                    if (isset($old['file'])) {
                        MediaHelper::delete($old['file']);
                    }
                }
                $avatarPayload = MediaHelper::upload($data['avatar'], 'employees/avatars');
                $userData['avatar'] = json_encode($avatarPayload);
            }

            $user->update($userData);

            // Sync Spatie Role
            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            // Update EmployeeDetail
            $existingDetail = $user->detail;
            $detailData = [
                'dob' => array_key_exists('dob', $data) ? $data['dob'] : $existingDetail?->dob,
                'gender' => $data['gender'] ?? ($existingDetail?->gender?->value ?? 'male'),
                'marital_status' => array_key_exists('marital_status', $data) ? $data['marital_status'] : $existingDetail?->marital_status?->value,
                'nid' => array_key_exists('nid', $data) ? $data['nid'] : $existingDetail?->nid,
                'blood_group' => array_key_exists('blood_group', $data) ? $data['blood_group'] : $existingDetail?->blood_group?->value,
                'department_id' => array_key_exists('department_id', $data) ? $data['department_id'] : $existingDetail?->department_id,
                'designation_id' => array_key_exists('designation_id', $data) ? $data['designation_id'] : $existingDetail?->designation_id,
                'shift_id' => array_key_exists('shift_id', $data) ? $data['shift_id'] : $existingDetail?->shift_id,
                'manager_id' => array_key_exists('manager_id', $data) ? $data['manager_id'] : $existingDetail?->manager_id,
                'joining_date' => array_key_exists('joining_date', $data) ? $data['joining_date'] : ($existingDetail?->joining_date ?? date('Y-m-d')),
                'confirmation_date' => array_key_exists('confirmation_date', $data) ? $data['confirmation_date'] : $existingDetail?->confirmation_date,
                'employment_type' => $data['employment_type'] ?? ($existingDetail?->employment_type?->value ?? 'full_time'),
                'basic_salary' => array_key_exists('basic_salary', $data) ? ($data['basic_salary'] ?? 0.00) : ($existingDetail?->basic_salary ?? 0.00),
                'country_id' => array_key_exists('country_id', $data) ? $data['country_id'] : $existingDetail?->country_id,
                'state_id' => array_key_exists('state_id', $data) ? $data['state_id'] : $existingDetail?->state_id,
                'city_id' => array_key_exists('city_id', $data) ? $data['city_id'] : $existingDetail?->city_id,
                'present_address' => array_key_exists('present_address', $data) ? $data['present_address'] : $existingDetail?->present_address,
                'permanent_address' => array_key_exists('permanent_address', $data) ? $data['permanent_address'] : $existingDetail?->permanent_address,
            ];

            $user->detail()->updateOrCreate(['user_id' => $user->id], $detailData);

            // Sync Primary Bank Account if provided
            if (! empty($data['bank']) && ! empty($data['account_no'])) {
                $primaryBank = $user->primaryBankAccount;
                if ($primaryBank) {
                    $primaryBank->update([
                        'bank' => $data['bank'],
                        'branch' => $data['branch'] ?? null,
                        'account_name' => $data['account_name'] ?? $user->name,
                        'account_no' => $data['account_no'],
                        'routing_number' => $data['routing_number'] ?? null,
                        'swift_code' => $data['swift_code'] ?? null,
                    ]);
                } else {
                    EmployeeBankAccount::create([
                        'user_id' => $user->id,
                        'bank' => $data['bank'],
                        'branch' => $data['branch'] ?? null,
                        'account_name' => $data['account_name'] ?? $user->name,
                        'account_no' => $data['account_no'],
                        'routing_number' => $data['routing_number'] ?? null,
                        'swift_code' => $data['swift_code'] ?? null,
                        'is_primary' => true,
                    ]);
                }
            }

            // Sync Primary Emergency Contact if provided
            if (! empty($data['emergency_name']) && ! empty($data['emergency_phone'])) {
                $contact = $user->emergencyContacts()->first();
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
                        'user_id' => $user->id,
                        'name' => $data['emergency_name'],
                        'relationship' => $data['emergency_relationship'] ?? 'Family',
                        'phone' => $data['emergency_phone'],
                        'alt_phone' => $data['emergency_alt_phone'] ?? null,
                        'address' => $data['emergency_address'] ?? null,
                    ]);
                }
            }

            return $user->load(['detail', 'roles', 'primaryBankAccount']);
        });
    }

    /**
     * Change employee status.
     */
    public function changeStatus(User $user, string|EmployeeStatusEnum $status): User
    {
        $statusVal = $status instanceof EmployeeStatusEnum ? $status->value : $status;
        $user->status = $statusVal;
        $user->save();

        return $user;
    }

    /**
     * Soft delete an employee (user).
     */
    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    /**
     * Restore a soft-deleted employee (user).
     */
    public function restore(int $userId): ?User
    {
        $user = User::withTrashed()->find($userId);
        if ($user) {
            $user->restore();
        }

        return $user;
    }

    /**
     * Add a document to an employee (user).
     */
    public function addDocument(User $user, array $data): EmployeeDocument
    {
        $docPayload = MediaHelper::upload($data['file'], 'employees/documents');

        return EmployeeDocument::create([
            'user_id' => $user->id,
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
