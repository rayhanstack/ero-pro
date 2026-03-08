<aside class="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-header">
        <i class="bi bi-box-seam fs-4 me-2"></i>
        <span>ERP Pro</span>
    </a>
    <div class="sidebar-nav">
        <x-sidebar.item route="dashboard" icon="bi-speedometer2" title="{{ _trans('common.Dashboard') }}" />

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
    <div class="sidebar-footer">
        <a href="login.html" class="user-panel">
            <img src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff" alt="User Avatar">
            <div class="user-panel-info">
                <p class="name">{{ _trans('common.Admin User') }}</p>
                <p class="role">{{ _trans('common.Super Admin') }}</p>
            </div>
            <i class="bi bi-box-arrow-right text-muted ms-auto" title="Logout"></i>
        </a>
    </div>
</aside>
