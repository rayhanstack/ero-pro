@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Projects</h3>
            <p class="text-muted mb-0">Manage all your active and pending projects</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <button class="btn btn-primary d-flex align-items-center"><i class="bi bi-plus-lg me-2"></i> New Project</button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" placeholder="Search projects...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select text-muted">
                        <option selected>Status: All</option>
                        <option value="1">In Progress</option>
                        <option value="2">Completed</option>
                        <option value="3">On Hold</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select text-muted">
                        <option selected>Client: All</option>
                        <option value="1">TechCorp Ltd</option>
                        <option value="2">Global Inc</option>
                        <option value="3">Startup LLC</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex justify-content-md-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-primary"><i class="bi bi-grid"></i></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"><i
                                class="bi bi-list-task"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="row g-4 mb-4">

        <!-- Card 1 -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 project-card shadow-sm position-relative">
                <div class="card-body">
                    <span class="badge bg-primary rounded-pill position-absolute top-0 end-0 m-3">In Progress</span>
                    <h6 class="fw-bold mb-1"><a href="project-detail.html" class="text-dark text-decoration-none">ERP
                            Module</a></h6>
                    <p class="text-primary small fw-bold mb-3">TechCorp Ltd</p>

                    <p class="text-muted small mb-3" style="min-height: 40px;">Developing the core enterprise resource
                        planning module with custom reporting.</p>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold">Progress</span>
                        <span class="small fw-bold text-primary">65%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 65%"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-group d-flex" style="margin-right: 10px;">
                                <img src="https://ui-avatars.com/api/?name=S+S&background=random"
                                    class="rounded-circle border border-white" width="30" height="30"
                                    style="margin-right: -10px; z-index: 3;" alt="Team">
                                <img src="https://ui-avatars.com/api/?name=J+D&background=random"
                                    class="rounded-circle border border-white" width="30" height="30"
                                    style="margin-right: -10px; z-index: 2;" alt="Team">
                                <div class="rounded-circle border border-white bg-light text-muted d-flex align-items-center justify-content-center small"
                                    style="width: 30px; height: 30px; z-index: 1;">+2</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted"><i class="bi bi-calendar3 me-1"></i> Oct 15</div>
                            <div class="small fw-bold mt-1">$12,000</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 pt-0">
                    <div class="d-flex gap-2">
                        <a href="project-detail.html" class="btn btn-sm btn-light flex-grow-1 text-primary"><i
                                class="bi bi-eye"></i> View</a>
                        <button class="btn btn-sm btn-light flex-grow-1 text-secondary"><i class="bi bi-pencil"></i>
                            Edit</button>
                        <button class="btn btn-sm btn-light text-danger px-3"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 project-card shadow-sm position-relative">
                <div class="card-body">
                    <span class="badge bg-success rounded-pill position-absolute top-0 end-0 m-3">Completed</span>
                    <h6 class="fw-bold mb-1"><a href="project-detail.html" class="text-dark text-decoration-none">Website
                            Redesign</a></h6>
                    <p class="text-primary small fw-bold mb-3">Global Inc</p>

                    <p class="text-muted small mb-3" style="min-height: 40px;">Redesigning corporate website to improve UX
                        and modern branding.</p>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold">Progress</span>
                        <span class="small fw-bold text-success">100%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-group d-flex" style="margin-right: 10px;">
                                <img src="https://ui-avatars.com/api/?name=A+B&background=random"
                                    class="rounded-circle border border-white" width="30" height="30"
                                    style="margin-right: -10px; z-index: 2;" alt="Team">
                                <img src="https://ui-avatars.com/api/?name=M+R&background=random"
                                    class="rounded-circle border border-white" width="30" height="30"
                                    style="z-index: 1;" alt="Team">
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted"><i class="bi bi-calendar3 me-1"></i> Oct 12</div>
                            <div class="small fw-bold mt-1">$8,500</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 pt-0">
                    <div class="d-flex gap-2">
                        <a href="project-detail.html" class="btn btn-sm btn-light flex-grow-1 text-primary"><i
                                class="bi bi-eye"></i> View</a>
                        <button class="btn btn-sm btn-light flex-grow-1 text-secondary"><i class="bi bi-pencil"></i>
                            Edit</button>
                        <button class="btn btn-sm btn-light text-danger px-3"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 project-card shadow-sm position-relative">
                <div class="card-body">
                    <span class="badge bg-warning text-dark rounded-pill position-absolute top-0 end-0 m-3">On Hold</span>
                    <h6 class="fw-bold mb-1"><a href="project-detail.html" class="text-dark text-decoration-none">Mobile
                            App iOS</a></h6>
                    <p class="text-primary small fw-bold mb-3">Startup LLC</p>

                    <p class="text-muted small mb-3" style="min-height: 40px;">Native iOS application for customer loyalty
                        rewards with AR features.</p>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold">Progress</span>
                        <span class="small fw-bold text-warning">30%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-group d-flex" style="margin-right: 10px;">
                                <img src="https://ui-avatars.com/api/?name=T+L&background=random"
                                    class="rounded-circle border border-white" width="30" height="30"
                                    style="margin-right: -10px; z-index: 3;" alt="Team">
                                <div class="rounded-circle border border-white bg-light text-muted d-flex align-items-center justify-content-center small"
                                    style="width: 30px; height: 30px; z-index: 1;">+1</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted"><i class="bi bi-calendar3 me-1"></i> Nov 01</div>
                            <div class="small fw-bold mt-1">$22,000</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 pt-0">
                    <div class="d-flex gap-2">
                        <a href="project-detail.html" class="btn btn-sm btn-light flex-grow-1 text-primary"><i
                                class="bi bi-eye"></i> View</a>
                        <button class="btn btn-sm btn-light flex-grow-1 text-secondary"><i class="bi bi-pencil"></i>
                            Edit</button>
                        <button class="btn btn-sm btn-light text-danger px-3"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 project-card shadow-sm position-relative">
                <div class="card-body">
                    <span class="badge bg-primary rounded-pill position-absolute top-0 end-0 m-3">In Progress</span>
                    <h6 class="fw-bold mb-1"><a href="project-detail.html" class="text-dark text-decoration-none">SEO
                            Audit</a></h6>
                    <p class="text-primary small fw-bold mb-3">Marketing Hub</p>

                    <p class="text-muted small mb-3" style="min-height: 40px;">Deep technical SEO audit and backlink
                        strategy implementation.</p>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold">Progress</span>
                        <span class="small fw-bold text-primary">85%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 85%"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-group d-flex" style="margin-right: 10px;">
                                <img src="https://ui-avatars.com/api/?name=R+A&background=random"
                                    class="rounded-circle border border-white" width="30" height="30"
                                    style="margin-right: -10px; z-index: 2;" alt="Team">
                                <img src="https://ui-avatars.com/api/?name=P+M&background=random"
                                    class="rounded-circle border border-white" width="30" height="30"
                                    style="z-index: 1;" alt="Team">
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted"><i class="bi bi-calendar3 me-1"></i> Oct 20</div>
                            <div class="small fw-bold mt-1">$4,000</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 pt-0">
                    <div class="d-flex gap-2">
                        <a href="project-detail.html" class="btn btn-sm btn-light flex-grow-1 text-primary"><i
                                class="bi bi-eye"></i> View</a>
                        <button class="btn btn-sm btn-light flex-grow-1 text-secondary"><i class="bi bi-pencil"></i>
                            Edit</button>
                        <button class="btn btn-sm btn-light text-danger px-3"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 5 -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 project-card shadow-sm position-relative">
                <div class="card-body">
                    <span class="badge bg-danger rounded-pill position-absolute top-0 end-0 m-3">Delayed</span>
                    <h6 class="fw-bold mb-1"><a href="project-detail.html"
                            class="text-dark text-decoration-none">Database Migration</a></h6>
                    <p class="text-primary small fw-bold mb-3">DataFlow Systems</p>

                    <p class="text-muted small mb-3" style="min-height: 40px;">Migrating legacy SQL on-prem database to
                        AWS cloud infrastructure.</p>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold">Progress</span>
                        <span class="small fw-bold text-danger">45%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 45%"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-group d-flex" style="margin-right: 10px;">
                                <img src="https://ui-avatars.com/api/?name=B+K&background=random"
                                    class="rounded-circle border border-white" width="30" height="30"
                                    style="margin-right: -10px; z-index: 3;" alt="Team">
                                <img src="https://ui-avatars.com/api/?name=S+P&background=random"
                                    class="rounded-circle border border-white" width="30" height="30"
                                    style="margin-right: -10px; z-index: 2;" alt="Team">
                                <div class="rounded-circle border border-white bg-light text-muted d-flex align-items-center justify-content-center small"
                                    style="width: 30px; height: 30px; z-index: 1;">+3</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted"><i class="bi bi-calendar3 me-1 text-danger"></i> Oct 05</div>
                            <div class="small fw-bold mt-1">$18,500</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 pt-0">
                    <div class="d-flex gap-2">
                        <a href="project-detail.html" class="btn btn-sm btn-light flex-grow-1 text-primary"><i
                                class="bi bi-eye"></i> View</a>
                        <button class="btn btn-sm btn-light flex-grow-1 text-secondary"><i class="bi bi-pencil"></i>
                            Edit</button>
                        <button class="btn btn-sm btn-light text-danger px-3"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 6 -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 project-card shadow-sm position-relative">
                <div class="card-body">
                    <span class="badge bg-secondary rounded-pill position-absolute top-0 end-0 m-3">Draft</span>
                    <h6 class="fw-bold mb-1"><a href="project-detail.html"
                            class="text-dark text-decoration-none">E-commerce API</a></h6>
                    <p class="text-primary small fw-bold mb-3">Retail Co</p>

                    <p class="text-muted small mb-3" style="min-height: 40px;">Building REST API for the new e-commerce
                        storefront app.</p>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold">Progress</span>
                        <span class="small fw-bold text-secondary">0%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-secondary" role="progressbar" style="width: 0%"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                        <div class="d-flex align-items-center">
                            <span class="small text-muted fst-italic">Unassigned</span>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted"><i class="bi bi-calendar3 me-1"></i> TBD</div>
                            <div class="small fw-bold mt-1">$9,000</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 pt-0">
                    <div class="d-flex gap-2">
                        <a href="project-detail.html" class="btn btn-sm btn-light flex-grow-1 text-primary"><i
                                class="bi bi-eye"></i> View</a>
                        <button class="btn btn-sm btn-light flex-grow-1 text-secondary"><i class="bi bi-pencil"></i>
                            Edit</button>
                        <button class="btn btn-sm btn-light text-danger px-3"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Pagination -->
    <nav aria-label="Project pagination" class="d-flex justify-content-center">
        <ul class="pagination">
            <li class="page-item disabled">
                <a class="page-link border-0 text-muted" href="#" tabindex="-1" aria-disabled="true"><i
                        class="bi bi-chevron-left"></i></a>
            </li>
            <li class="page-item active" aria-current="page"><a class="page-link border-0 rounded mx-1"
                    href="#">1</a></li>
            <li class="page-item"><a class="page-link border-0 rounded text-dark mx-1" href="#">2</a></li>
            <li class="page-item"><a class="page-link border-0 rounded text-dark mx-1" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link border-0 text-dark" href="#"><i class="bi bi-chevron-right"></i></a>
            </li>
        </ul>
    </nav>
@endsection
