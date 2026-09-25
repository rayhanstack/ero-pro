@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    @push('css')
        <style>
            .settings-sidebar .nav-link {
                padding: 12px 18px;
                color: #64748b;
                border-radius: 8px;
                margin-bottom: 5px;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .settings-sidebar .nav-link:hover,
            .settings-sidebar .nav-link.active {
                background-color: #eef2ff;
                color: #4f46e5;
                font-weight: 600;
            }

            .settings-sidebar .nav-link i {
                width: 20px;
                display: inline-block;
            }
        </style>
    @endpush

    <x-ui.page-header
        title="{{ _trans('common.Settings') }}"
        subtitle="{{ _trans('common.Configure company, localization, attendance, leave, payroll and system preferences') }}"
        :breadcrumbs="[
            ['label' => _trans('common.Dashboard'), 'url' => route('dashboard')],
            ['label' => _trans('common.Settings')],
        ]"
    />

    <div class="row g-4">
        <!-- Left Sidebar (Settings Nav) -->
        @include('admin.setting.inc.sidebar')

        <!-- Right Content Area -->
        <div class="col-xl-9 col-lg-8">
            <div class="tab-content" id="v-pills-tabContent">

                <!-- Profile Settings -->
                @include('admin.setting.inc.profile')

                <!-- Company Details & Branding -->
                @include('admin.setting.inc.company-details')

                <!-- Localization & Formats -->
                @include('admin.setting.inc.localization')

                <!-- Attendance Rules -->
                @include('admin.setting.inc.attendance')

                <!-- Leave Quota & Policies -->
                @include('admin.setting.inc.leave')

                <!-- Payroll Calculation -->
                @include('admin.setting.inc.payroll')

                <!-- Mail & SMTP Configuration -->
                @include('admin.setting.inc.mail')

                <!-- Security -->
                @include('admin.setting.inc.security')

                <!-- Notifications -->
                @include('admin.setting.inc.notifications')

            </div>
        </div>
    </div>
@endsection
