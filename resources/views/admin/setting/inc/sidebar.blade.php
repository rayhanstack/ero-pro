@php
    $activeTab = request('tab');
    if (!$activeTab) {
        if ($errors->has('current_password') || $errors->has('password')) {
            $activeTab = 'security';
        } else {
            $activeTab = 'profile';
        }
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
                {{ _trans('common.Branding') }}
            </button>
            <button class="nav-link text-start {{ $activeTab === 'security' ? 'active' : '' }}" id="v-pills-security-tab" data-bs-toggle="pill"
                data-bs-target="#v-pills-security" type="button" role="tab">
                <i class="bi bi-shield-lock me-2"></i>
                {{ _trans('common.Security') }}
            </button>
            <button class="nav-link text-start {{ $activeTab === 'notifications' ? 'active' : '' }}" id="v-pills-notifications-tab" data-bs-toggle="pill"
                data-bs-target="#v-pills-notifications" type="button" role="tab">
                <i class="bi bi-bell me-2"></i>
                {{ _trans('common.Notifications') }}
            </button>
        </div>
    </div>
</div>
