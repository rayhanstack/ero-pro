@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    @push('css')
        <style>
            .settings-sidebar .nav-link {
                padding: 12px 20px;
                color: #64748b;
                /* var(--secondary) */
                border-radius: 8px;
                margin-bottom: 5px;
                font-weight: 500;
            }

            .settings-sidebar .nav-link:hover,
            .settings-sidebar .nav-link.active {
                background-color: #eef2ff;
                color: #4f46e5;
                /* var(--primary) */
            }

            .settings-sidebar .nav-link i {
                width: 24px;
                display: inline-block;
            }
        </style>
    @endpush
    <h3 class="fw-bold mb-4">{{ $title }}</h3>

    <div class="row g-4">
        <!-- Left Sidebar (Settings Nav) -->
        @include('admin.setting.inc.sidebar')

        <!-- Right Content Area -->
        <div class="col-xl-9 col-lg-8">
            <div class="tab-content" id="v-pills-tabContent">

                <!-- Profile Settings -->
                @include('admin.setting.inc.profile')

                <!-- Company Details -->
                @include('admin.setting.inc.company-details')

                <!-- Security -->
                @include('admin.setting.inc.security')

                <!-- Notifications -->
                @include('admin.setting.inc.notifications')

            </div>
        </div>
    </div>
@endsection
