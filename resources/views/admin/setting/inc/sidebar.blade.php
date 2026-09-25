@php
    $activeTab = request('tab', 'profile');
    if ($errors->has('current_password') || $errors->has('password')) {
        $activeTab = 'security';
    }
@endphp

<div class="col-xl-3 col-lg-4">
    <div class="card h-100 p-2 shadow-sm border-0">
        <div class="nav flex-column nav-pills settings-sidebar" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            <button class="nav-link text-start {{ $activeTab === 'profile' ? 'active' : '' }}" id="v-pills-profile-tab" data-bs-toggle="pill"
                data-bs-target="#v-pills-profile" type="button" role="tab">
                <i class="bi bi-person-badge me-2"></i>
                {{ _trans('common.My Profile') }}
            </button>

            <button class="nav-link text-start {{ $activeTab === 'company' ? 'active' : '' }}" id="v-pills-company-tab" data-bs-toggle="pill"
                data-bs-target="#v-pills-company" type="button" role="tab">
                <i class="bi bi-building me-2"></i>
                {{ _trans('common.Company & Branding') }}
            </button>

            <button class="nav-link text-start {{ $activeTab === 'localization' ? 'active' : '' }}" id="v-pills-localization-tab" data-bs-toggle="pill"
                data-bs-target="#v-pills-localization" type="button" role="tab">
                <i class="bi bi-globe me-2"></i>
                {{ _trans('common.Localization') }}
            </button>

            <button class="nav-link text-start {{ $activeTab === 'attendance' ? 'active' : '' }}" id="v-pills-attendance-tab" data-bs-toggle="pill"
                data-bs-target="#v-pills-attendance" type="button" role="tab">
                <i class="bi bi-clock-history me-2"></i>
                {{ _trans('common.Attendance Rules') }}
            </button>

            <button class="nav-link text-start {{ $activeTab === 'leave' ? 'active' : '' }}" id="v-pills-leave-tab" data-bs-toggle="pill"
                data-bs-target="#v-pills-leave" type="button" role="tab">
                <i class="bi bi-calendar-check me-2"></i>
                {{ _trans('common.Leave Settings') }}
            </button>

            <button class="nav-link text-start {{ $activeTab === 'payroll' ? 'active' : '' }}" id="v-pills-payroll-tab" data-bs-toggle="pill"
                data-bs-target="#v-pills-payroll" type="button" role="tab">
                <i class="bi bi-cash-stack me-2"></i>
                {{ _trans('common.Payroll Settings') }}
            </button>

            <button class="nav-link text-start {{ $activeTab === 'mail' ? 'active' : '' }}" id="v-pills-mail-tab" data-bs-toggle="pill"
                data-bs-target="#v-pills-mail" type="button" role="tab">
                <i class="bi bi-envelope-at me-2"></i>
                {{ _trans('common.Mail Configuration') }}
            </button>

            <button class="nav-link text-start {{ $activeTab === 'security' ? 'active' : '' }}" id="v-pills-security-tab" data-bs-toggle="pill"
                data-bs-target="#v-pills-security" type="button" role="tab">
                <i class="bi bi-shield-lock me-2"></i>
                {{ _trans('common.Security') }}
            </button>
        </div>
    </div>
</div>
