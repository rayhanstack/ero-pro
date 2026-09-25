<aside class="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-header">
        <i class="bi bi-box-seam fs-4 me-2"></i>
        <span>ERP Pro</span>
    </a>
    <div class="sidebar-nav">
        <x-sidebar.item route="dashboard" icon="bi-speedometer2" title="{{ _trans('common.Dashboard') }}" />
        <x-sidebar.item route="components" icon="bi-grid" title="{{ _trans('common.Components') }}" />

        <!-- Submenu Example -->
        <x-sidebar.sub-menu id="pagesSubmenu" icon="bi-layers" title="{{ _trans('common.Pages') }}">
            <x-sidebar.sub-item url="blank-page.html" title="{{ _trans('common.Blank Page') }}" />
        </x-sidebar.sub-menu>

        <x-sidebar.item route="project" icon="bi-folder2-open" title="{{ _trans('common.Projects') }}" />
        <x-sidebar.item route="task" icon="bi-check2-square" title="{{ _trans('common.Tasks') }}" />
        <x-sidebar.item route="client" icon="bi-people" title="{{ _trans('common.Clients') }}" />
        <x-sidebar.item url="team.html" icon="bi-person-badge" title="{{ _trans('common.Team') }}" />
        <x-sidebar.item url="finance.html" icon="bi-receipt" title="{{ _trans('common.Finance') }}" />
        <x-sidebar.item url="reports.html" icon="bi-bar-chart-line" title="{{ _trans('common.Reports') }}" />
        <x-sidebar.item url="form-elements.html" icon="bi-ui-checks" title="{{ _trans('common.Forms') }}" />
        <x-sidebar.item url="ai-assistant.html" icon="bi-robot" title="{{ _trans('common.AI Assistant') }}" />
        <x-sidebar.item route="settings" icon="bi-gear" title="{{ _trans('common.Settings') }}" />
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
