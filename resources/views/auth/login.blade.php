@extends('admin.layouts.guest')
@section('title', _trans('common.Sign In'))

@section('content')
    <div class="auth-header">
        <a href="{{ url('/') }}" class="auth-brand">
            <i class="bi bi-box-seam fs-3"></i>
            <span>ERP Pro</span>
        </a>
        <h4 class="fw-bold text-dark mb-1">{{ _trans('common.Welcome Back') }}</h4>
        <p class="text-muted small mb-0">{{ _trans('common.Please enter your credentials to sign in') }}</p>
    </div>

    <div class="auth-body">
        <form method="POST" action="{{ route('login') }}" class="needs-validation">
            @csrf

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
                        value="{{ old('email') }}"
                        required
                        autofocus>
                </div>
                @error('email')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label fw-semibold small text-muted mb-0">{{ _trans('common.Password') }}</label>
                    <a href="{{ route('password.request') }}" class="text-primary small text-decoration-none fw-semibold">
                        {{ _trans('common.Forgot password?') }}
                    </a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password"
                        name="password"
                        id="password"
                        class="form-control border-start-0 @error('password') is-invalid @enderror"
                        placeholder="••••••••"
                        required>
                </div>
                @error('password')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label small text-muted" for="remember">
                        {{ _trans('common.Remember me on this device') }}
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2">
                <span>{{ _trans('common.Sign In') }}</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </form>
    </div>
@endsection
