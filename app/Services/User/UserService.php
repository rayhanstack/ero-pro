<?php

namespace App\Services\User;

use App\Helpers\MediaHelper;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * Get paginated users with eager-loaded roles and search filters.
     */
    public function getPaginatedUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with('roles')
            ->orderBy('id', 'desc');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new user with role and avatar.
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $avatarPayload = null;
            if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
                $avatarPayload = MediaHelper::upload($data['avatar'], 'users/avatars');
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? 'active',
                'time_zone' => $data['time_zone'] ?? 'UTC',
                'avatar' => $avatarPayload ? json_encode($avatarPayload) : null,
                'email_verified_at' => now(),
            ]);

            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            return $user;
        });
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? $user->status,
                'time_zone' => $data['time_zone'] ?? $user->time_zone,
            ];

            if (! empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
                if ($user->avatar) {
                    $old = json_decode($user->avatar, true);
                    if (isset($old['file'])) {
                        MediaHelper::delete($old['file']);
                    }
                }
                $avatarPayload = MediaHelper::upload($data['avatar'], 'users/avatars');
                $updateData['avatar'] = json_encode($avatarPayload);
            }

            $user->update($updateData);

            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            return $user;
        });
    }

    /**
     * Toggle user status between active and inactive.
     */
    public function toggleStatus(User $user): User
    {
        if ($user->id === Auth::id()) {
            throw new \InvalidArgumentException(_trans('common.You cannot deactivate your own account.'));
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return $user;
    }

    /**
     * Delete user with safeguards.
     */
    public function deleteUser(User $user): bool
    {
        if ($user->id === Auth::id()) {
            throw new \InvalidArgumentException(_trans('common.You cannot delete your own account.'));
        }

        if ($user->hasRole('Super Admin') && User::role('Super Admin')->count() <= 1) {
            throw new \InvalidArgumentException(_trans('common.The primary Super Admin cannot be deleted.'));
        }

        return DB::transaction(function () use ($user) {
            if ($user->avatar) {
                $old = json_decode($user->avatar, true);
                if (isset($old['file'])) {
                    MediaHelper::delete($old['file']);
                }
            }

            $user->syncRoles([]);

            return (bool) $user->delete();
        });
    }
}
