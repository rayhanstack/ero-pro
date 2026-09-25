<?php

namespace App\Services\Profile;

use App\Helpers\MediaHelper;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    /**
     * Update user profile information.
     */
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $oldAttributes = [
                'name' => $user->name,
                'phone' => $user->phone,
                'time_zone' => $user->time_zone,
            ];

            $updateData = [
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'time_zone' => $data['time_zone'] ?? $user->time_zone,
            ];

            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
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

            ActivityLog::log(
                action: 'profile.updated',
                subject: $user,
                old: $oldAttributes,
                new: [
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'time_zone' => $user->time_zone,
                ],
                userId: $user->id
            );

            return $user;
        });
    }

    /**
     * Update user password.
     */
    public function updatePassword(User $user, string $newPassword): bool
    {
        return DB::transaction(function () use ($user, $newPassword) {
            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            ActivityLog::log(
                action: 'password.changed',
                subject: $user,
                userId: $user->id
            );

            return true;
        });
    }
}
