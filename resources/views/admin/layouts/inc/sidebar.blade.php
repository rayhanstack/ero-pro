<aside class="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-header">
        <i class="bi bi-box-seam fs-4 me-2"></i>
        <span>ERP Pro</span>
    </a>
    <div class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="sidebar-nav__item active" data-bs-title="Dashboard">
            <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
        </a>

        <!-- Submenu Example -->
        <div class="nav-item-wrapper">
            <a href="#pagesSubmenu" data-bs-toggle="collapse" class="sidebar-nav__item" aria-expanded="false"
                data-bs-title="Pages">
                <i class="bi bi-layers"></i>
                <span class="flex-grow-1">Pages</span>
                <i class="bi bi-chevron-down ms-auto sub-menu-icon" style="font-size: 14px; margin-right: 0;"></i>
            </a>
            <div class="collapse" id="pagesSubmenu">
                <div class="bg-light bg-opacity-50 ps-4 py-2">
                    <a href="blank-page.html" class="sidebar-nav__item py-2 text-muted" data-bs-title="Blank Page"
                        style="font-size: 0.9rem;">
                        <i class="bi bi-circle small" style="font-size: 10px;"></i> <span>Blank Page</span>
                    </a>
                </div>
            </div>
        </div>

        <a href="projects.html" class="sidebar-nav__item" data-bs-title="Projects">
            <i class="bi bi-folder2-open"></i> <span>Projects</span>
        </a>
        <a href="tasks.html" class="sidebar-nav__item" data-bs-title="Tasks">
            <i class="bi bi-check2-square"></i> <span>Tasks</span>
        </a>
        <a href="clients.html" class="sidebar-nav__item" data-bs-title="Clients">
            <i class="bi bi-people"></i> <span>Clients</span>
        </a>
        <a href="team.html" class="sidebar-nav__item" data-bs-title="Team">
            <i class="bi bi-person-badge"></i> <span>Team</span>
        </a>
        <a href="finance.html" class="sidebar-nav__item" data-bs-title="Finance">
            <i class="bi bi-receipt"></i> <span>Finance</span>
        </a>
        <a href="reports.html" class="sidebar-nav__item" data-bs-title="Reports">
            <i class="bi bi-bar-chart-line"></i> <span>Reports</span>
        </a>
        <a href="form-elements.html" class="sidebar-nav__item" data-bs-title="Forms">
            <i class="bi bi-ui-checks"></i> <span>Forms</span>
        </a>
        <a href="ai-assistant.html" class="sidebar-nav__item" data-bs-title="AI Assistant">
            <i class="bi bi-robot"></i> <span>AI Assistant</span>
        </a>
        <a href="settings.html" class="sidebar-nav__item" data-bs-title="Settings">
            <i class="bi bi-gear"></i> <span>Settings</span>
        </a>
    </div>
    <div class="sidebar-footer">
        <a href="login.html" class="user-panel">
            <img src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff" alt="User Avatar">
            <div class="user-panel-info">
                <p class="name">Admin User</p>
                <p class="role">Super Admin</p>
            </div>
            <i class="bi bi-box-arrow-right text-muted ms-auto" title="Logout"></i>
        </a>
    </div>
</aside>
