@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white h-100 p-3 mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-white-50">Total Clients</h6>
                        <h3 class="fw-bold mb-0">87</h3>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-people"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white h-100 p-3 mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-white-50">Active Clients</h6>
                        <h3 class="fw-bold mb-0">72</h3>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-person-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white h-100 p-3 mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-white-50">New This Month</h6>
                        <h3 class="fw-bold mb-0">+5</h3>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-person-plus"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-dark h-100 p-3 mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 text-dark text-opacity-75">Total Revenue</h6>
                        <h3 class="fw-bold mb-0">$248K</h3>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-currency-dollar"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" placeholder="Search clients...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select text-muted">
                        <option selected>Status: All</option>
                        <option value="1">Active</option>
                        <option value="2">Inactive</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex justify-content-md-end">
                    <button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Client</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Grid -->
    <div class="row g-4">
        <!-- Cards -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 text-center">
                <div class="card-body pt-4">
                    <span
                        class="badge bg-success bg-opacity-10 text-success rounded-pill position-absolute top-0 end-0 m-3">Active</span>
                    <img src="https://ui-avatars.com/api/?name=Tech+Corp&background=random" class="rounded-circle mb-3"
                        width="64" height="64" alt="Client Logo">
                    <h5 class="fw-bold mb-1">TechCorp Ltd</h5>
                    <p class="text-muted small mb-3">CEO: David Miller</p>

                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <button class="btn btn-sm btn-light rounded-circle"><i
                                class="bi bi-envelope text-primary"></i></button>
                        <button class="btn btn-sm btn-light rounded-circle"><i
                                class="bi bi-telephone text-success"></i></button>
                    </div>

                    <div class="row text-center border-top border-bottom py-2 mx-0 mb-3 bg-light rounded">
                        <div class="col-6 border-end">
                            <h6 class="fw-bold mb-0">5</h6>
                            <span class="small text-muted">Projects</span>
                        </div>
                        <div class="col-6">
                            <h6 class="fw-bold mb-0">$45.2k</h6>
                            <span class="small text-muted">Revenue</span>
                        </div>
                    </div>

                    <a href="client-detail.html" class="btn btn-outline-primary w-100">View Profile</a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card h-100 text-center">
                <div class="card-body pt-4">
                    <span
                        class="badge bg-success bg-opacity-10 text-success rounded-pill position-absolute top-0 end-0 m-3">Active</span>
                    <img src="https://ui-avatars.com/api/?name=Global+Inc&background=random" class="rounded-circle mb-3"
                        width="64" height="64" alt="Client Logo">
                    <h5 class="fw-bold mb-1">Global Inc</h5>
                    <p class="text-muted small mb-3">MD: Sarah Johnson</p>

                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <button class="btn btn-sm btn-light rounded-circle"><i
                                class="bi bi-envelope text-primary"></i></button>
                        <button class="btn btn-sm btn-light rounded-circle"><i
                                class="bi bi-telephone text-success"></i></button>
                    </div>

                    <div class="row text-center border-top border-bottom py-2 mx-0 mb-3 bg-light rounded">
                        <div class="col-6 border-end">
                            <h6 class="fw-bold mb-0">3</h6>
                            <span class="small text-muted">Projects</span>
                        </div>
                        <div class="col-6">
                            <h6 class="fw-bold mb-0">$28.5k</h6>
                            <span class="small text-muted">Revenue</span>
                        </div>
                    </div>

                    <a href="client-detail.html" class="btn btn-outline-primary w-100">View Profile</a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card h-100 text-center">
                <div class="card-body pt-4">
                    <span
                        class="badge bg-danger bg-opacity-10 text-danger rounded-pill position-absolute top-0 end-0 m-3">Inactive</span>
                    <img src="https://ui-avatars.com/api/?name=Startup+LLC&background=random" class="rounded-circle mb-3"
                        width="64" height="64" alt="Client Logo">
                    <h5 class="fw-bold mb-1">Startup LLC</h5>
                    <p class="text-muted small mb-3">Founder: Chris Evans</p>

                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <button class="btn btn-sm btn-light rounded-circle"><i
                                class="bi bi-envelope text-primary"></i></button>
                        <button class="btn btn-sm btn-light rounded-circle"><i
                                class="bi bi-telephone text-success"></i></button>
                    </div>

                    <div class="row text-center border-top border-bottom py-2 mx-0 mb-3 bg-light rounded">
                        <div class="col-6 border-end">
                            <h6 class="fw-bold mb-0">2</h6>
                            <span class="small text-muted">Projects</span>
                        </div>
                        <div class="col-6">
                            <h6 class="fw-bold mb-0">$15.4k</h6>
                            <span class="small text-muted">Revenue</span>
                        </div>
                    </div>

                    <a href="client-detail.html" class="btn btn-outline-primary w-100">View Profile</a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card h-100 text-center">
                <div class="card-body pt-4">
                    <span
                        class="badge bg-success bg-opacity-10 text-success rounded-pill position-absolute top-0 end-0 m-3">Active</span>
                    <img src="https://ui-avatars.com/api/?name=Mkt+Hub&background=random" class="rounded-circle mb-3"
                        width="64" height="64" alt="Client Logo">
                    <h5 class="fw-bold mb-1">Marketing Hub</h5>
                    <p class="text-muted small mb-3">Marketing Dir: Emma Watson</p>

                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <button class="btn btn-sm btn-light rounded-circle"><i
                                class="bi bi-envelope text-primary"></i></button>
                        <button class="btn btn-sm btn-light rounded-circle"><i
                                class="bi bi-telephone text-success"></i></button>
                    </div>

                    <div class="row text-center border-top border-bottom py-2 mx-0 mb-3 bg-light rounded">
                        <div class="col-6 border-end">
                            <h6 class="fw-bold mb-0">4</h6>
                            <span class="small text-muted">Projects</span>
                        </div>
                        <div class="col-6">
                            <h6 class="fw-bold mb-0">$12.8k</h6>
                            <span class="small text-muted">Revenue</span>
                        </div>
                    </div>

                    <a href="client-detail.html" class="btn btn-outline-primary w-100">View Profile</a>
                </div>
            </div>
        </div>

    </div>
@endsection
