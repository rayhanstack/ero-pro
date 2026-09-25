@extends('admin.layouts.guest')
@section('title', _trans('common.Reset Password'))

@section('content')
    <div class="auth-header">
        <a href="{{ url('/') }}" class="auth-brand">
            <i class="bi bi-box-seam fs-3"></i>
            <span>ERP Pro</span>
        </a>
        <h4 class="fw-bold text-dark mb-1">{{ _trans('common.Set New Password') }}</h4>
        <p class="text-muted small mb-0">{{ _trans('common.Please choose a strong and secure password') }}</p>
    </div>

    <div class="auth-body">
        <form method="POST" action="{{ route('password.update') }}" class="needs-validation">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold small text-muted">{{ _trans('common.Email Address') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email"
                        name="email"
                        id="email"
                        class="form-control border-start-0 @error('email') is-invalid @enderror"
                        placeholder="name@company.com"
                        value="{{ old('email', $email) }}"
                        required>
                </div>
                @error('email')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold small text-muted">{{ _trans('common.New Password') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password"
                        name="password"
                        id="password"
                        class="form-control border-start-0 @error('password') is-invalid @enderror"
                        placeholder="••••••••"
                        required
                        autofocus>
                </div>
                @error('password')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-semibold small text-muted">{{ _trans('common.Confirm New Password') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0">
                        <i class="bi bi-lock-fill"></i>
                    </span>
                    <input type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        class="form-control border-start-0"
                        placeholder="••••••••"
                        required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
                {{ _trans('common.Reset Password') }}
            </button>

            <div class="text-center">
                <a href="{{ route('login') }}" class="text-muted small text-decoration-none d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i>
                    <span>{{ _trans('common.Back to Sign In') }}</span>
                </a>
            </div>
        </form>
    </div>
@endsection
