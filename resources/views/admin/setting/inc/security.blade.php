@php
    $activeTab = request('tab');
    if (!$activeTab) {
        $activeTab = ($errors->has('current_password') || $errors->has('password')) ? 'security' : 'profile';
    }
@endphp

<div class="tab-pane fade {{ $activeTab === 'security' ? 'show active' : '' }}" id="v-pills-security" role="tabpanel">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4 p-md-5">
            <h5 class="card-title fw-bold mb-4">{{ _trans('common.Change Password') }}</h5>

            <form method="POST" action="{{ route('profile.password') }}" class="needs-validation">
                @csrf
                @method('PUT')

                <div class="mb-3 col-md-8">
                    <label for="current_password" class="form-label fw-semibold small">{{ _trans('common.Current Password') }} <span class="text-danger">*</span></label>
                    <input type="password"
                        name="current_password"
                        id="current_password"
                        class="form-control @error('current_password') is-invalid @enderror"
                        placeholder="••••••••"
                        required>
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 col-md-8">
                    <label for="security_password" class="form-label fw-semibold small">{{ _trans('common.New Password') }} <span class="text-danger">*</span></label>
                    <input type="password"
                        name="password"
                        id="security_password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="••••••••"
                        required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 col-md-8">
                    <label for="password_confirmation" class="form-label fw-semibold small">{{ _trans('common.Confirm New Password') }} <span class="text-danger">*</span></label>
                    <input type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        class="form-control"
                        placeholder="••••••••"
                        required>
                </div>

                <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock"></i>
                    <span>{{ _trans('common.Update Password') }}</span>
                </button>
            </form>
        </div>
    </div>
</div>
