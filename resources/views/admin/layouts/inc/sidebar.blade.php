<aside class="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-header d-flex align-items-center">
        @if (globalSetting('company_logo'))
            <img src="{{ globalSetting('company_logo') }}" alt="{{ globalSetting('company_name', 'ERP Pro') }}" class="sidebar-logo me-2" style="max-height: 32px; max-width: 140px; object-fit: contain;">
        @else
            <i class="bi bi-box-seam fs-4 me-2"></i>
            <span>{{ globalSetting('company_name', 'ERP Pro') }}</span>
        @endif
    </a>
    <div class="sidebar-nav">
        @can('dashboard.view')
            <x-sidebar.item route="dashboard" icon="bi-speedometer2" title="{{ _trans('common.Dashboard') }}" />
            <x-sidebar.item route="components" icon="bi-grid" title="{{ _trans('common.Components') }}" />
        @endcan

        @can('project.view')
            <x-sidebar.item route="project" icon="bi-folder2-open" title="{{ _trans('common.Projects') }}" />
        @endcan

        @can('task.view')
            <x-sidebar.item route="task" icon="bi-check2-square" title="{{ _trans('common.Tasks') }}" />
        @endcan

        @can('client.view')
            <x-sidebar.item route="client" icon="bi-person-lines-fill" title="{{ _trans('common.Clients') }}" />
        @endcan

        @if(hasAnyPermission(['employee.view', 'department.view', 'designation.view', 'shift.view', 'weekend.view', 'holiday.view']))
            <x-sidebar.sub-menu id="hrMenu" icon="bi-briefcase" title="{{ _trans('common.HR') }}" :active="request()->routeIs('employees.*') || request()->routeIs('departments.*') || request()->routeIs('designations.*') || request()->routeIs('shifts.*') || request()->routeIs('weekends.*') || request()->routeIs('holidays.*')">
                @can('employee.view')
                    <x-sidebar.sub-item route="employees.index" title="{{ _trans('common.Employees') }}" />
                @endcan
                @can('department.view')
                    <x-sidebar.sub-item route="departments.index" title="{{ _trans('common.Departments') }}" />
                @endcan
                @can('designation.view')
                    <x-sidebar.sub-item route="designations.index" title="{{ _trans('common.Designations') }}" />
                @endcan
                @can('shift.view')
                    <x-sidebar.sub-item route="shifts.index" title="{{ _trans('common.Shifts') }}" />
                @endcan
                @can('weekend.view')
                    <x-sidebar.sub-item route="weekends.index" title="{{ _trans('common.Weekends') }}" />
                @endcan
                @can('holiday.view')
                    <x-sidebar.sub-item route="holidays.index" title="{{ _trans('common.Holidays') }}" />
                @endcan
            </x-sidebar.sub-menu>
        @endif

        @if(hasAnyPermission(['attendance.view', 'attendance.manage']))
            <x-sidebar.sub-menu id="attendanceMenu" icon="bi-calendar-check" title="{{ _trans('common.Attendance') }}" :active="request()->routeIs('attendances.*')">
                @can('attendance.view')
                    <x-sidebar.sub-item route="attendances.my" :patterns="['attendances.my', 'attendances.punch*']" title="{{ _trans('common.My Attendance') }}" />
                @endcan
                @can('attendance.manage')
                    <x-sidebar.sub-item route="attendances.daily" :patterns="['attendances.daily', 'attendances.store', 'attendances.update', 'attendances.destroy']" title="{{ _trans('common.Daily Attendance') }}" />
                    <x-sidebar.sub-item route="attendances.monthly" title="{{ _trans('common.Monthly Attendance') }}" />
                @endcan
                @can('attendance.view')
                    <x-sidebar.sub-item route="attendances.regularizations" :patterns="['attendances.regularizations*']" title="{{ _trans('common.Regularizations') }}" />
                @endcan
            </x-sidebar.sub-menu>
        @endif
        @can('role.view')
            <x-sidebar.item route="roles.index" icon="bi-shield-lock" title="{{ _trans('common.Roles & Permissions') }}" />
        @endcan

        @can('setting.view')
            <x-sidebar.item route="settings" icon="bi-gear" title="{{ _trans('common.Settings') }}" />
            <x-sidebar.item route="languages.index" icon="bi-translate" title="{{ _trans('common.Languages') }}" />
            <x-sidebar.item route="activity-logs.index" icon="bi-clock-history" title="{{ _trans('common.Activity Logs') }}" />
        @endcan
    </div>
    <div class="sidebar-footer p-3 border-top">
        <div class="user-panel d-flex align-items-center justify-content-between w-100">
            <a href="{{ route('settings') }}" class="d-flex align-items-center text-decoration-none text-dark flex-grow-1 min-w-0 me-2">
                <img src="{{ Auth::user()?->avatar_url ?? 'https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff' }}" class="rounded-circle object-fit-cover me-2 flex-shrink-0" width="36" height="36" alt="{{ Auth::user()?->name ?? 'User' }}">
                <div class="user-panel-info text-truncate">
                    <p class="name mb-0 text-truncate fw-semibold small text-dark">{{ Auth::user()?->name ?? _trans('common.Admin User') }}</p>
                    <p class="role mb-0 small text-muted text-truncate" style="font-size: 11px;">{{ Auth::user()?->role_name ?? _trans('common.Super Admin') }}</p>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="m-0 flex-shrink-0">
                @csrf
                <button type="submit" class="btn btn-link p-1 text-muted text-hover-danger" title="{{ _trans('common.Logout') }}">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
