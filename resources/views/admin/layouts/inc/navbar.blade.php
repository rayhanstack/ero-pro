<header class="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">{{ $title ?? _trans('common.Dashboard') }}</h1>
    </div>
    <div class="topbar-right d-flex align-items-center gap-2">
        <div class="search-box d-none d-md-block">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control" placeholder="{{ _trans('common.Search here...') }}">
        </div>

        <!-- Quick Punch In/Out Widget -->
        @auth
            @can('attendance.view')
                @php
                    $navTodayAtt = \App\Models\Attendance::where('employee_id', Auth::id())
                        ->whereDate('date', today())
                        ->first();
                    $navCheckedIn = $navTodayAtt && $navTodayAtt->check_in !== null;
                    $navCheckedOut = $navTodayAtt && $navTodayAtt->check_out !== null;
                @endphp
                <div class="d-none d-sm-flex align-items-center me-1">
                    @if (! $navCheckedIn)
                        <form method="POST" action="{{ route('attendances.punch') }}" class="m-0">
                            @csrf
                            <input type="hidden" name="type" value="in">
                            <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-1.5 px-2.5 py-1.5 rounded-3 fw-semibold shadow-xs" title="{{ _trans('common.Punch In Now') }}">
                                <i class="bi bi-box-arrow-in-right"></i>
                                <span class="small">{{ _trans('common.Punch In') }}</span>
                            </button>
                        </form>
                    @elseif ($navCheckedIn && ! $navCheckedOut)
                        <form method="POST" action="{{ route('attendances.punch') }}" class="m-0">
                            @csrf
                            <input type="hidden" name="type" value="out">
                            <button type="submit" class="btn btn-sm btn-warning d-flex align-items-center gap-1.5 px-2.5 py-1.5 rounded-3 fw-semibold text-dark shadow-xs" title="{{ _trans('common.Punch Out') }} ({{ _trans('common.In at') }} {{ $navTodayAtt->check_in_time }})">
                                <i class="bi bi-box-arrow-right"></i>
                                <span class="small">{{ _trans('common.Punch Out') }}</span>
                                <span class="badge bg-dark-subtle text-dark ms-1 font-monospace">{{ $navTodayAtt->check_in_time }}</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('attendances.my') }}" class="btn btn-sm btn-light border d-flex align-items-center gap-1 px-2.5 py-1.5 rounded-3 text-muted" title="{{ _trans('common.Completed today') }}: {{ $navTodayAtt->work_duration_formatted }}">
                            <i class="bi bi-check-circle-fill text-success small"></i>
                            <span class="small fw-semibold text-dark">{{ $navTodayAtt->work_duration_formatted }}</span>
                        </a>
                    @endif
                </div>
            @endcan
        @endauth

        <!-- Language Switcher -->
        @php
            $activeLanguages = \App\Models\Language::where('status', 'active')->get();
            $currentLocale = app()->getLocale();
        @endphp
        <div class="dropdown">
            <button class="btn btn-sm btn-light border d-flex align-items-center gap-1 px-2.5 py-1.5 rounded-3" data-bs-toggle="dropdown" aria-expanded="false" title="{{ _trans('common.Change Language') }}">
                <i class="bi bi-translate text-primary"></i>
                <span class="small fw-semibold text-uppercase">{{ $currentLocale }}</span>
                <i class="bi bi-chevron-down text-muted" style="font-size: 10px;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-md border-0 py-1" style="min-width: 160px;">
                @forelse ($activeLanguages as $lang)
                    <li>
                        <a class="dropdown-item py-1.5 px-3 small d-flex align-items-center justify-content-between {{ $currentLocale === $lang->code ? 'active' : '' }}" href="{{ route('locale.switch', $lang->code) }}">
                            <span>{{ $lang->name }} <span class="text-muted">({{ $lang->native }})</span></span>
                            @if ($currentLocale === $lang->code)
                                <i class="bi bi-check2 text-primary"></i>
                            @endif
                        </a>
                    </li>
                @empty
                    <li><span class="dropdown-item py-1 px-3 small text-muted">English</span></li>
                @endforelse
            </ul>
        </div>

        <div class="dropdown">
            <button class="nav-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                <i class="bi bi-bell"></i>
                <span class="badge bg-danger rounded-pill">5</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-0 shadow-md border-0" style="width: 300px;">
                <li class="p-3 border-bottom fw-bold">{{ _trans('common.Notifications') }}</li>
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
                <li class="p-2 text-center border-top"><a href="#" class="text-decoration-none small">{{ _trans('common.View All Notifications') }}</a></li>
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
                    <a class="dropdown-item py-2" href="{{ route('settings', ['tab' => 'profile']) }}">
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
