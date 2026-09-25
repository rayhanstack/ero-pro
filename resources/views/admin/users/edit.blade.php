@extends('admin.layouts.app')
@section('title', _trans('common.Edit User'))

@section('content')
    <x-ui.page-header
        title="{{ _trans('common.Edit User') }}: {{ $user->name }}"
        subtitle="{{ _trans('common.Update user details, role assignment, and status') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.User Management'), 'url' => route('users.index')],
            ['label' => $user->name],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>{{ _trans('common.Back to Users') }}</span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" class="needs-validation">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <x-ui.card :title="_trans('common.User Information')" icon="bi-person" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">{{ _trans('common.Full Name') }} <span class="text-danger">*</span></label>
                            <input type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">{{ _trans('common.Email Address') }} <span class="text-danger">*</span></label>
                            <input type="email"
                                name="email"
                                id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}"
                                required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold">{{ _trans('common.New Password') }}</label>
                            <input type="password"
                                name="password"
                                id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="{{ _trans('common.Leave blank to keep current password') }}">
                            <small class="text-muted">{{ _trans('common.Leave blank if you do not want to change password') }}</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">{{ _trans('common.Phone Number') }}</label>
                            <input type="text"
                                name="phone"
                                id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="time_zone" class="form-label fw-semibold">{{ _trans('common.Timezone') }}</label>
                            <select name="time_zone" id="time_zone" class="form-select @error('time_zone') is-invalid @enderror">
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

                        <div class="col-md-6">
                            <label for="avatar" class="form-label fw-semibold">{{ _trans('common.Avatar Image') }}</label>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle object-fit-cover shadow-xs" width="45" height="45">
                                <input type="file"
                                    name="avatar"
                                    id="avatar"
                                    class="form-control @error('avatar') is-invalid @enderror"
                                    accept="image/*">
                            </div>
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <div class="col-lg-4">
                <x-ui.card :title="_trans('common.Role & Status')" icon="bi-shield-check" class="mb-4">
                    <div class="mb-3">
                        <label for="role" class="form-label fw-semibold">{{ _trans('common.Assign Role') }} <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">{{ _trans('common.Select Role') }}</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role', $userRole) === $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">{{ _trans('common.Account Status') }} <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>{{ _trans('common.Active') }}</option>
                            <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>{{ _trans('common.Inactive') }}</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </x-ui.card>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-check-lg fs-5"></i>
                        <span>{{ _trans('common.Save Changes') }}</span>
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-light py-2">{{ _trans('common.Cancel') }}</a>
                </div>
            </div>
        </div>
    </form>
@endsection
