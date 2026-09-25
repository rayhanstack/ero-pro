<aside class="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-header">
        <i class="bi bi-box-seam fs-4 me-2"></i>
        <span>ERP Pro</span>
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

        @can('user.view')
            <x-sidebar.item route="users.index" icon="bi-people" title="{{ _trans('common.Users') }}" />
        @endcan

        @can('role.view')
            <x-sidebar.item route="roles.index" icon="bi-shield-lock" title="{{ _trans('common.Roles & Permissions') }}" />
        @endcan

        @can('setting.view')
            <x-sidebar.item route="settings" icon="bi-gear" title="{{ _trans('common.Settings') }}" />
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
