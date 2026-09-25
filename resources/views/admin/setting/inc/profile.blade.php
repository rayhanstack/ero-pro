@php
    $user = Auth::user();
    $activeTab = request('tab');
    if (!$activeTab) {
        $activeTab = ($errors->has('current_password') || $errors->has('password')) ? 'security' : 'profile';
    }
@endphp

<div class="tab-pane fade {{ $activeTab === 'profile' ? 'show active' : '' }}" id="v-pills-profile" role="tabpanel">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h5 class="card-title fw-bold mb-4">{{ _trans('common.Public Profile') }}</h5>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="needs-validation">
                @csrf
                @method('PUT')

                <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                    <img id="profileAvatarPreview"
                        src="{{ $user->avatar_url }}"
                        class="rounded-circle object-fit-cover me-4 shadow-sm border"
                        width="80"
                        height="80"
                        alt="{{ $user->name }}">
                    <div>
                        <label class="btn btn-sm btn-primary mb-2 cursor-pointer">
                            <i class="bi bi-upload me-1"></i>
                            <span>{{ _trans('common.Upload new avatar') }}</span>
                            <input type="file"
                                name="avatar"
                                id="profileAvatarInput"
                                class="d-none"
                                accept="image/*">
                        </label>
                        <p class="small text-muted mb-0">{{ _trans('common.Recommended size: 400x400px. Standard image files (JPG, PNG, WebP) up to 2MB.') }}</p>
                        @error('avatar')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="profile_name" class="form-label fw-semibold small">{{ _trans('common.Full Name') }} <span class="text-danger">*</span></label>
                        <input type="text"
                            name="name"
                            id="profile_name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}"
                            required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="profile_email" class="form-label fw-semibold small">{{ _trans('common.Email Address') }}</label>
                        <input type="email"
                            id="profile_email"
                            class="form-control bg-light"
                            value="{{ $user->email }}"
                            readonly
                            disabled>
                        <small class="text-muted">{{ _trans('common.Email cannot be changed directly.') }}</small>
                    </div>

                    <div class="col-md-6">
                        <label for="profile_phone" class="form-label fw-semibold small">{{ _trans('common.Phone Number') }}</label>
                        <input type="tel"
                            name="phone"
                            id="profile_phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $user->phone) }}"
                            placeholder="+8801700000000">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="profile_timezone" class="form-label fw-semibold small">{{ _trans('common.Timezone') }}</label>
                        <select name="time_zone" id="profile_timezone" class="form-select @error('time_zone') is-invalid @enderror">
                            <option value="UTC" {{ old('time_zone', $user->time_zone) === 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="Asia/Dhaka" {{ old('time_zone', $user->time_zone) === 'Asia/Dhaka' ? 'selected' : '' }}>Asia/Dhaka (GMT+6)</option>
                            <option value="America/New_York" {{ old('time_zone', $user->time_zone) === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                            <option value="Europe/London" {{ old('time_zone', $user->time_zone) === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                            <option value="Asia/Dubai" {{ old('time_zone', $user->time_zone) === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)</option>
                        </select>
                        @error('time_zone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Save Changes') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const avatarInput = document.getElementById('profileAvatarInput');
        const avatarPreview = document.getElementById('profileAvatarPreview');

        if (avatarInput && avatarPreview) {
            avatarInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        avatarPreview.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endpush
