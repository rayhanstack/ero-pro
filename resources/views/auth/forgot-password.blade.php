@extends('admin.layouts.guest')
@section('title', _trans('common.Forgot Password'))

@section('content')
    <div class="auth-header">
        <a href="{{ url('/') }}" class="auth-brand">
            <i class="bi bi-box-seam fs-3"></i>
            <span>ERP Pro</span>
        </a>
        <h4 class="fw-bold text-dark mb-1">{{ _trans('common.Forgot Password') }}</h4>
        <p class="text-muted small mb-0">{{ _trans('common.Enter your registered email to receive a password reset link') }}</p>
    </div>

    <div class="auth-body">
        <form method="POST" action="{{ route('password.email') }}" class="needs-validation">
            @csrf

            <div class="mb-4">
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

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3">
                {{ _trans('common.Send Reset Link') }}
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
