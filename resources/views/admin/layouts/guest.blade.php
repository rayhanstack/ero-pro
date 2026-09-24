<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? (View::hasSection('title') ? View::getSection('title') : config('app.name', 'ERP Pro')) }}</title>

    <!-- Local CSS Assets -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/plus-jakarta-sans.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/app.css') }}">
    @stack('css')

    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 2rem 1rem;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .auth-header {
            text-align: center;
            padding: 2.5rem 2rem 1.5rem;
        }

        .auth-body {
            padding: 0 2rem 2.5rem;
        }

        .auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: #4f46e5;
            text-decoration: none;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <!-- Flash Messages -->
    @include('admin.layouts.inc.flash-messages')

    <div class="auth-wrapper">
        <div class="auth-card">
            @yield('content')
        </div>
    </div>

    <!-- Modals Stack -->
    @stack('modals')

    <!-- Local JS Scripts -->
    <script src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/app.js') }}"></script>
    @stack('script')
</body>

</html>
