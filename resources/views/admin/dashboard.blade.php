@extends('admin.layouts.app')
@section('title', $title)
@section('content')

    <!-- Section 1: Stats Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="text-muted mb-1">Total Revenue</h6>
                        <h3 class="fw-bold mb-0" data-countup="48295">$0</h3>
                    </div>
                    <div class="icon-circle bg-gradient-primary">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill"><i
                            class="bi bi-arrow-up-short"></i> +12.5%</span>
                    <span class="text-muted small ms-1">vs last month</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="text-muted mb-1">Active Projects</h6>
                        <h3 class="fw-bold mb-0" data-countup="24">0</h3>
                    </div>
                    <div class="icon-circle bg-gradient-info">
                        <i class="bi bi-folder2-open"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill">3 new this week</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="text-muted mb-1">Pending Tasks</h6>
                        <h3 class="fw-bold mb-0" data-countup="138">0</h3>
                    </div>
                    <div class="icon-circle bg-gradient-warning">
                        <i class="bi bi-check2-square"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill"><i
                            class="bi bi-arrow-down-short"></i> 12 overdue</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="text-muted mb-1">Total Clients</h6>
                        <h3 class="fw-bold mb-0" data-countup="87">0</h3>
                    </div>
                    <div class="icon-circle bg-gradient-success">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill"><i
                            class="bi bi-arrow-up-short"></i> +5 this month</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">Revenue Overview</h5>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">Project Status</h5>
                    <div
                        style="position: relative; height: 300px; width: 100%; display: flex; align-items: center; justify-content: center;">
                        <canvas id="statusChart"></canvas>
                        <div class="position-absolute text-center"
                            style="top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;">
                            <h4 class="fw-bold mb-0">24</h4>
                            <span class="text-muted small">Total</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Projects Table + Activity Feed -->
    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-body p-0">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title fw-bold mb-0">Recent Projects</h5>
                        <a href="projects.html" class="btn btn-sm btn-light">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Project</th>
                                    <th>Client</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 text-muted">1</td>
                                    <td class="fw-bold">ERP Module</td>
                                    <td>TechCorp Ltd</td>
                                    <td>Oct 15</td>
                                    <td><span class="badge bg-primary rounded-pill">In Progress</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2 small">65%</span>
                                            <div class="progress" style="height: 6px; width: 60px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 65%">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="project-detail.html" class="btn btn-sm btn-light text-primary"><i
                                                class="bi bi-eye"></i></a>
                                        <button class="btn btn-sm btn-light text-secondary"><i
                                                class="bi bi-pencil"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 text-muted">2</td>
                                    <td class="fw-bold">Website Redesign</td>
                                    <td>Global Inc</td>
                                    <td>Oct 12</td>
                                    <td><span class="badge bg-success rounded-pill">Completed</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2 small">100%</span>
                                            <div class="progress" style="height: 6px; width: 60px;">
                                                <div class="progress-bar bg-success" role="progressbar"
                                                    style="width: 100%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="project-detail.html" class="btn btn-sm btn-light text-primary"><i
                                                class="bi bi-eye"></i></a>
                                        <button class="btn btn-sm btn-light text-secondary"><i
                                                class="bi bi-pencil"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 text-muted">3</td>
                                    <td class="fw-bold">Mobile App iOS</td>
                                    <td>Startup LLC</td>
                                    <td>Nov 01</td>
                                    <td><span class="badge bg-warning text-dark rounded-pill">On
                                            Hold</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2 small">30%</span>
                                            <div class="progress" style="height: 6px; width: 60px;">
                                                <div class="progress-bar bg-warning" role="progressbar"
                                                    style="width: 30%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="project-detail.html" class="btn btn-sm btn-light text-primary"><i
                                                class="bi bi-eye"></i></a>
                                        <button class="btn btn-sm btn-light text-secondary"><i
                                                class="bi bi-pencil"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 text-muted">4</td>
                                    <td class="fw-bold">SEO Audit</td>
                                    <td>Marketing Hub</td>
                                    <td>Oct 20</td>
                                    <td><span class="badge bg-primary rounded-pill">In Progress</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2 small">85%</span>
                                            <div class="progress" style="height: 6px; width: 60px;">
                                                <div class="progress-bar bg-primary" role="progressbar"
                                                    style="width: 85%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="project-detail.html" class="btn btn-sm btn-light text-primary"><i
                                                class="bi bi-eye"></i></a>
                                        <button class="btn btn-sm btn-light text-secondary"><i
                                                class="bi bi-pencil"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 text-muted">5</td>
                                    <td class="fw-bold">Database Migration</td>
                                    <td>DataFlow Systems</td>
                                    <td>Oct 05</td>
                                    <td><span class="badge bg-danger rounded-pill">Delayed</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2 small">45%</span>
                                            <div class="progress" style="height: 6px; width: 60px;">
                                                <div class="progress-bar bg-danger" role="progressbar"
                                                    style="width: 45%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="project-detail.html" class="btn btn-sm btn-light text-primary"><i
                                                class="bi bi-eye"></i></a>
                                        <button class="btn btn-sm btn-light text-secondary"><i
                                                class="bi bi-pencil"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">Recent Activity</h5>
                    <div class="timeline-feed">
                        <div class="timeline-item">
                            <div class="timeline-dot bg-primary"></div>
                            <div class="timeline-content">New project <strong>'ERP Module'</strong> created
                            </div>
                            <div class="timeline-time">2h ago</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot bg-success"></div>
                            <div class="timeline-content">Invoice <strong>#1042</strong> marked as paid
                            </div>
                            <div class="timeline-time">4h ago</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot bg-info"></div>
                            <div class="timeline-content">Task <strong>'UI Design'</strong> completed by
                                Sara</div>
                            <div class="timeline-time">6h ago</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot bg-primary"></div>
                            <div class="timeline-content">New client <strong>'TechCorp Ltd'</strong> added
                            </div>
                            <div class="timeline-time">1d ago</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot bg-warning"></div>
                            <div class="timeline-content">Project deadline extended for
                                <strong>Apex</strong>
                            </div>
                            <div class="timeline-time">1d ago</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot bg-secondary"></div>
                            <div class="timeline-content">New team member joined: <strong>Rahim</strong>
                            </div>
                            <div class="timeline-time">2d ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Bottom Widgets Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">Top Clients</h5>

                    <div class="d-flex align-items-center mb-4">
                        <span class="text-muted small fw-bold me-3">#1</span>
                        <img src="https://ui-avatars.com/api/?name=Tech+Corp&background=random"
                            class="rounded-circle me-3" width="40" height="40" alt="Avatar">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold">TechCorp Ltd</h6>
                            <span class="small text-muted">5 Projects</span>
                        </div>
                        <div class="fw-bold text-dark">$45,200</div>
                    </div>

                    <div class="d-flex align-items-center mb-4">
                        <span class="text-muted small fw-bold me-3">#2</span>
                        <img src="https://ui-avatars.com/api/?name=Global+Inc&background=random"
                            class="rounded-circle me-3" width="40" height="40" alt="Avatar">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold">Global Inc</h6>
                            <span class="small text-muted">3 Projects</span>
                        </div>
                        <div class="fw-bold text-dark">$28,500</div>
                    </div>

                    <div class="d-flex align-items-center mb-4">
                        <span class="text-muted small fw-bold me-3">#3</span>
                        <img src="https://ui-avatars.com/api/?name=Strt+Up&background=random" class="rounded-circle me-3"
                            width="40" height="40" alt="Avatar">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold">Startup LLC</h6>
                            <span class="small text-muted">2 Projects</span>
                        </div>
                        <div class="fw-bold text-dark">$15,400</div>
                    </div>

                    <div class="d-flex align-items-center">
                        <span class="text-muted small fw-bold me-3">#4</span>
                        <img src="https://ui-avatars.com/api/?name=Mkt+Hub&background=random" class="rounded-circle me-3"
                            width="40" height="40" alt="Avatar">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold">Marketing Hub</h6>
                            <span class="small text-muted">4 Projects</span>
                        </div>
                        <div class="fw-bold text-dark">$12,800</div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">Upcoming Deadlines</h5>

                    <div class="p-3 border rounded mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0 fw-bold">Database Migration</h6>
                            <span class="badge bg-danger rounded-pill">Today</span>
                        </div>
                        <div class="small text-muted"><i class="bi bi-clock me-1"></i> Due in 5 hours
                        </div>
                    </div>

                    <div class="p-3 border rounded mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0 fw-bold">UI Kit Design</h6>
                            <span class="badge bg-warning text-dark rounded-pill">Tomorrow</span>
                        </div>
                        <div class="small text-muted"><i class="bi bi-clock me-1"></i> Oct 06, 2024</div>
                    </div>

                    <div class="p-3 border rounded mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0 fw-bold">Client Presentation</h6>
                            <span class="badge bg-info text-dark rounded-pill">3 Days</span>
                        </div>
                        <div class="small text-muted"><i class="bi bi-clock me-1"></i> Oct 08, 2024</div>
                    </div>

                    <div class="p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0 fw-bold">Website Redesign</h6>
                            <span class="badge bg-secondary rounded-pill">Next Week</span>
                        </div>
                        <div class="small text-muted"><i class="bi bi-clock me-1"></i> Oct 12, 2024</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">Team Workload</h5>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=Sara+Smith&background=random"
                                    class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                <h6 class="mb-0 fw-bold">Sara Smith</h6>
                            </div>
                            <span class="small fw-bold">12 Tasks</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 90%" aria-valuenow="90"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=John+Doe&background=random"
                                    class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                <h6 class="mb-0 fw-bold">John Doe</h6>
                            </div>
                            <span class="small fw-bold">8 Tasks</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 65%"
                                aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=Mike+Ross&background=random"
                                    class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                <h6 class="mb-0 fw-bold">Mike Ross</h6>
                            </div>
                            <span class="small fw-bold">5 Tasks</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 40%"
                                aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=Rahim+Ahmed&background=random"
                                    class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                <h6 class="mb-0 fw-bold">Rahim Ahmed</h6>
                            </div>
                            <span class="small fw-bold">3 Tasks</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 25%" aria-valuenow="25"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
