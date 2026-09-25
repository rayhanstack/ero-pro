<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\Profile\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    /**
     * Update authenticated user's profile.
     */
    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $this->profileService->updateProfile(Auth::user(), $request->validated());

        return redirect()
            ->route('settings', ['tab' => 'profile'])
            ->with('success', _trans('common.Profile updated successfully.'));
    }

    /**
     * Update authenticated user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->profileService->updatePassword(Auth::user(), $request->validated('password'));

        return redirect()
            ->route('settings', ['tab' => 'security'])
            ->with('success', _trans('common.Password updated successfully.'));
    }
}
