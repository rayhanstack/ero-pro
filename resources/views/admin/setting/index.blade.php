@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    @push('css')
        <style>
            .settings-sidebar .nav-link {
                padding: 12px 20px;
                color: #64748b;
                /* var(--secondary) */
                border-radius: 8px;
                margin-bottom: 5px;
                font-weight: 500;
            }

            .settings-sidebar .nav-link:hover,
            .settings-sidebar .nav-link.active {
                background-color: #eef2ff;
                color: #4f46e5;
                /* var(--primary) */
            }

            .settings-sidebar .nav-link i {
                width: 24px;
                display: inline-block;
            }
        </style>
    @endpush
    <h3 class="fw-bold mb-4">Account Settings</h3>

    <div class="row g-4">
        <!-- Left Sidebar (Settings Nav) -->
        <div class="col-xl-3 col-lg-4">
            <div class="card h-100 p-2">
                <div class="nav flex-column nav-pills settings-sidebar" id="v-pills-tab" role="tablist"
                    aria-orientation="vertical">
                    <button class="nav-link active text-start" id="v-pills-profile-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-profile" type="button" role="tab"><i class="bi bi-person-badge"></i>
                        My Profile</button>
                    <button class="nav-link text-start" id="v-pills-company-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-company" type="button" role="tab"><i class="bi bi-building"></i>
                        Company Details</button>
                    <button class="nav-link text-start" id="v-pills-security-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-security" type="button" role="tab"><i class="bi bi-shield-lock"></i>
                        Security</button>
                    <button class="nav-link text-start" id="v-pills-notifications-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-notifications" type="button" role="tab"><i class="bi bi-bell"></i>
                        Notifications</button>
                    <hr class="my-2">
                    <button class="nav-link text-start text-danger" type="button"><i class="bi bi-trash"></i> Delete
                        Account</button>
                </div>
            </div>
        </div>

        <!-- Right Content Area -->
        <div class="col-xl-9 col-lg-8">
            <div class="tab-content" id="v-pills-tabContent">

                <!-- Profile Settings -->
                <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel">
                    <div class="card">
                        <div class="card-body p-4 p-md-5">
                            <h5 class="card-title fw-bold mb-4">Public Profile</h5>

                            <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                                <img src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff"
                                    class="rounded-circle me-4" width="80" height="80" alt="Avatar">
                                <div>
                                    <div class="d-flex gap-2 mb-2">
                                        <button class="btn btn-sm btn-primary">Upload new</button>
                                        <button class="btn btn-sm btn-light text-danger">Remove</button>
                                    </div>
                                    <p class="small text-muted mb-0">Recommended size: 400x400px. Standard image files only.
                                    </p>
                                </div>
                            </div>

                            <form class="needs-validation" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">First Name</label>
                                        <input type="text" class="form-control" value="Admin" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Last Name</label>
                                        <input type="text" class="form-control" value="User" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Email Address</label>
                                        <input type="email" class="form-control" value="admin@erppro.com" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Phone Number</label>
                                        <input type="tel" class="form-control" value="+1 (555) 123-4567">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">Bio</label>
                                        <textarea class="form-control" rows="3" placeholder="Write a short summary about yourself..."></textarea>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Company Details -->
                <div class="tab-pane fade" id="v-pills-company" role="tabpanel">
                    <div class="card mb-4">
                        <div class="card-body p-4 p-md-5">
                            <h5 class="card-title fw-bold mb-4">Company Details</h5>
                            <form>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">Company Name</label>
                                        <input type="text" class="form-control" value="ERP Pro Solutions Inc.">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">Address</label>
                                        <input type="text" class="form-control"
                                            value="123 Tech Boulevard, Suite 400, San Francisco, CA 94105">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Tax ID / VAT</label>
                                        <input type="text" class="form-control" value="US-123456789">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Industry</label>
                                        <select class="form-select text-muted">
                                            <option>Software</option>
                                            <option>Consulting</option>
                                            <option>Finance</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-primary px-4">Update Details</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security -->
                <div class="tab-pane fade" id="v-pills-security" role="tabpanel">
                    <div class="card mb-4">
                        <div class="card-body p-4 p-md-5">
                            <h5 class="card-title fw-bold mb-4">Change Password</h5>
                            <form>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Current Password</label>
                                    <input type="password" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">New Password</label>
                                    <input type="password" class="form-control">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold small">Confirm New Password</label>
                                    <input type="password" class="form-control">
                                </div>
                                <button type="button" class="btn btn-primary">Update Password</button>
                            </form>

                            <hr class="my-5">

                            <h5 class="card-title fw-bold mb-4">Two-Factor Authentication</h5>
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                                <div>
                                    <h6 class="fw-bold mb-1">Authenticator App</h6>
                                    <p class="small text-muted mb-0">Use an app like Google Authenticator to get a security
                                        code.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input fs-4" type="checkbox" role="switch" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="tab-pane fade" id="v-pills-notifications" role="tabpanel">
                    <div class="card mb-4">
                        <div class="card-body p-4 p-md-5">
                            <h5 class="card-title fw-bold mb-4">Email Notifications</h5>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1">Weekly reports</h6>
                                    <p class="small text-muted mb-0">Receive a weekly digest of project progress.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input fs-5" type="checkbox" role="switch" checked>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1">New Invoices</h6>
                                    <p class="small text-muted mb-0">Alert when a new invoice is generated.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input fs-5" type="checkbox" role="switch" checked>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1">Security Alerts</h6>
                                    <p class="small text-muted mb-0">Critical alerts about account security.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input fs-5" type="checkbox" role="switch" checked disabled>
                                </div>
                            </div>

                            <button class="btn btn-primary mt-4">Save Preferences</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
