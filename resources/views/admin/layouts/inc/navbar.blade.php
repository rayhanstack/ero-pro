<header class="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">{{ $title ?? _trans('common.Dashboard') }}</h1>
    </div>
    <div class="topbar-right">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control" placeholder="Search here...">
        </div>
        <div class="dropdown">
            <button class="nav-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                <i class="bi bi-bell"></i>
                <span class="badge bg-danger rounded-pill">5</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-0 shadow-md border-0" style="width: 300px;">
                <li class="p-3 border-bottom fw-bold">Notifications</li>
                <li><a class="dropdown-item py-2 border-bottom" href="#"><span
                            class="badge bg-primary rounded-pill me-2">New</span> Sara completed UI
                        Design</a></li>
                <li><a class="dropdown-item py-2 border-bottom" href="#"><span
                            class="badge bg-success rounded-pill me-2">Info</span> Invoice #1042 paid</a>
                </li>
                <li><a class="dropdown-item py-2 border-bottom" href="#"><span
                            class="badge bg-warning rounded-pill me-2">Alert</span> Apex deadline
                        extended</a></li>
                <li><a class="dropdown-item py-2" href="#"><span
                            class="badge bg-info rounded-pill me-2">Sys</span> System update tonight</a>
                </li>
                <li class="p-2 text-center border-top"><a href="#" class="text-decoration-none small">View All
                        Notifications</a></li>
            </ul>
        </div>
        <div class="dropdown">
            <a href="#" class="topbar-user d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ Auth::user()?->avatar_url ?? 'https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff' }}" alt="{{ Auth::user()?->name ?? 'User' }}" class="rounded-circle object-fit-cover" width="36" height="36">
                <span class="d-none d-md-inline fw-semibold small ms-2 text-dark">{{ Auth::user()?->name ?? _trans('common.Admin User') }}</span>
                <i class="bi bi-chevron-down small text-muted ms-1 d-none d-sm-inline-block"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-md border-0 py-2">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold text-dark">{{ Auth::user()?->name ?? _trans('common.Admin User') }}</div>
                    <div class="small text-muted text-truncate">{{ Auth::user()?->email }}</div>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="{{ route('settings') }}">
                        <i class="bi bi-person me-2 text-primary"></i>
                        {{ _trans('common.Profile') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="{{ route('settings') }}">
                        <i class="bi bi-gear me-2 text-secondary"></i>
                        {{ _trans('common.Settings') }}
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider my-1">
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 text-danger d-flex align-items-center">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            <span>{{ _trans('common.Logout') }}</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
