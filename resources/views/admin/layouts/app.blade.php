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
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/app.css') }}">
    @stack('css')
</head>

<body>

    <div class="wrapper">
        <!-- ===== SIDEBAR ===== -->
        @include('admin.layouts.inc.sidebar')

        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay"></div>

        <!-- ===== MAIN CONTENT ===== -->
        <main class="main-content">
            <!-- TOP NAVBAR -->
            @include('admin.layouts.inc.navbar')

            <!-- PAGE CONTENT -->
            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Modals Stack -->
    @stack('modals')

    <!-- Flash Toast Messages -->
    @include('admin.layouts.inc.flash-messages')

    <!-- Bootstrap Toast for Welcome Message -->
    @include('admin.layouts.inc.welcome')

    <!-- Local JS Scripts -->
    <script src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/app.js') }}"></script>
    @stack('script')
</body>

</html>
