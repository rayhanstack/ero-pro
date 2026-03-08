@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Task Board</h3>
        <div>
            <button class="btn btn-outline-secondary me-2"><i class="bi bi-filter"></i> Filter</button>
            <button class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Task</button>
        </div>
    </div>

    <div class="kanban-board">

        <!-- COLUMN 1: TO DO -->
        <div class="col-xl-3 col-md-6 min-vw-25 kanban-column" style="min-width: 320px;">
            <div class="bg-light rounded p-3 h-100">
                <div class="kanban-col-header border-start border-4 border-info ps-2 mb-3">
                    <h6 class="fw-bold mb-0">To Do</h6>
                    <span class="badge bg-secondary text-dark rounded-pill bg-opacity-25">4</span>
                </div>

                <div class="kanban-tasks pb-2" style="min-height: 200px;">
                    <!-- Task Card -->
                    <div class="card task-card shadow-sm mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-danger rounded-pill bg-opacity-10 text-danger">High</span>
                                <i class="bi bi-grip-horizontal text-muted grip-handle pointer-event"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Database Migration</h6>
                            <p class="text-primary small mb-2 fw-bold">DataFlow Systems</p>
                            <p class="text-muted small mb-3 text-truncate">Migrate SQL to AWS cloud</p>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small text-danger fw-bold"><i class="bi bi-calendar3 me-1"></i>Oct 05</span>
                                <span class="small text-muted"><i
                                        class="bi bi-check2-square text-success me-1"></i>2/5</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                <img src="https://ui-avatars.com/api/?name=J+D&background=random" class="rounded-circle"
                                    width="28" height="28" alt="Team">
                                <div class="text-muted small">
                                    <i class="bi bi-chat-dots me-1"></i> 3
                                    <i class="bi bi-paperclip ms-2 me-1"></i> 1
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Task Card -->
                    <div class="card task-card shadow-sm mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-warning text-dark rounded-pill bg-opacity-10">Medium</span>
                                <i class="bi bi-grip-horizontal text-muted grip-handle pointer-event"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Set up Staging Env</h6>
                            <p class="text-primary small mb-2 fw-bold">ERP Module</p>
                            <p class="text-muted small mb-3 text-truncate">Configure staging servers.</p>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small text-muted"><i class="bi bi-calendar3 me-1"></i>Oct 10</span>
                                <span class="small text-muted"><i
                                        class="bi bi-check2-square text-success me-1"></i>0/2</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                <div class="rounded-circle border border-dashed text-muted d-flex align-items-center justify-content-center"
                                    style="width: 28px; height: 28px;"><i class="bi bi-person"></i></div>
                                <div class="text-muted small">
                                    <i class="bi bi-chat-dots me-1"></i> 0
                                    <i class="bi bi-paperclip ms-2 me-1"></i> 0
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-secondary w-100 border-dashed"><i class="bi bi-plus"></i> Add
                    Task</button>
            </div>
        </div>

        <!-- COLUMN 2: IN PROGRESS -->
        <div class="col-xl-3 col-md-6 min-vw-25 kanban-column" style="min-width: 320px;">
            <div class="bg-light rounded p-3 h-100">
                <div class="kanban-col-header border-start border-4 border-warning ps-2 mb-3">
                    <h6 class="fw-bold mb-0">In Progress</h6>
                    <span class="badge bg-secondary text-dark rounded-pill bg-opacity-25">3</span>
                </div>

                <div class="kanban-tasks pb-2" style="min-height: 200px;">
                    <!-- Task Card -->
                    <div class="card task-card shadow-sm mb-3 border-start border-3 border-warning">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-danger rounded-pill bg-opacity-10 text-danger">High</span>
                                <i class="bi bi-grip-horizontal text-muted grip-handle pointer-event"></i>
                            </div>
                            <h6 class="fw-bold mb-1">SEO Audit Phase 1</h6>
                            <p class="text-primary small mb-2 fw-bold">Marketing Hub</p>
                            <p class="text-muted small mb-3 text-truncate">Deep technical crawler check</p>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small text-muted"><i class="bi bi-calendar3 me-1"></i>Oct 08</span>
                                <span class="small text-muted"><i
                                        class="bi bi-check2-square text-success me-1"></i>4/6</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                <img src="https://ui-avatars.com/api/?name=P+M&background=random" class="rounded-circle"
                                    width="28" height="28" alt="Team">
                                <div class="text-muted small">
                                    <i class="bi bi-chat-dots me-1"></i> 5
                                    <i class="bi bi-paperclip ms-2 me-1"></i> 2
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-secondary w-100 border-dashed"><i class="bi bi-plus"></i> Add
                    Task</button>
            </div>
        </div>

        <!-- COLUMN 3: IN REVIEW -->
        <div class="col-xl-3 col-md-6 min-vw-25 kanban-column" style="min-width: 320px;">
            <div class="bg-light rounded p-3 h-100">
                <div class="kanban-col-header border-start border-4 border-primary ps-2 mb-3">
                    <h6 class="fw-bold mb-0">In Review</h6>
                    <span class="badge bg-secondary text-dark rounded-pill bg-opacity-25">2</span>
                </div>

                <div class="kanban-tasks pb-2" style="min-height: 200px;">
                    <!-- Task Card -->
                    <div class="card task-card shadow-sm mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-info text-info rounded-pill bg-opacity-10">Low</span>
                                <i class="bi bi-grip-horizontal text-muted grip-handle pointer-event"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Dashboard UI Mockups</h6>
                            <p class="text-primary small mb-2 fw-bold">ERP Module</p>
                            <p class="text-muted small mb-3 text-truncate">Final review of light/dark modes</p>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small text-muted"><i class="bi bi-calendar3 me-1"></i>Oct 05</span>
                                <span class="small text-muted"><i
                                        class="bi bi-check2-square text-success me-1"></i>3/3</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                <img src="https://ui-avatars.com/api/?name=R+A&background=random" class="rounded-circle"
                                    width="28" height="28" alt="Team">
                                <div class="text-muted small">
                                    <i class="bi bi-chat-dots me-1"></i> 12
                                    <i class="bi bi-paperclip ms-2 me-1"></i> 4
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-secondary w-100 border-dashed"><i class="bi bi-plus"></i> Add
                    Task</button>
            </div>
        </div>

        <!-- COLUMN 4: DONE -->
        <div class="col-xl-3 col-md-6 min-vw-25 kanban-column" style="min-width: 320px;">
            <div class="bg-light rounded p-3 h-100">
                <div class="kanban-col-header border-start border-4 border-success ps-2 mb-3">
                    <h6 class="fw-bold mb-0">Done</h6>
                    <span class="badge bg-secondary text-dark rounded-pill bg-opacity-25">5</span>
                </div>

                <div class="kanban-tasks pb-2" style="min-height: 200px;">
                    <!-- Task Card -->
                    <div class="card task-card shadow-sm mb-3 bg-white opacity-75">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-warning text-dark rounded-pill bg-opacity-10">Medium</span>
                                <i class="bi bi-grip-horizontal text-muted grip-handle pointer-event"></i>
                            </div>
                            <h6 class="fw-bold mb-1 text-decoration-line-through">API Authentication</h6>
                            <p class="text-primary small mb-2 fw-bold text-decoration-line-through">ERP Module</p>
                            <p class="text-muted small mb-3 text-truncate text-decoration-line-through">Setup OAuth2
                                endpoints</p>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small text-success fw-bold"><i
                                        class="bi bi-check-circle-fill me-1"></i>Done</span>
                                <span class="small text-muted"><i
                                        class="bi bi-check2-square text-success me-1"></i>4/4</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                <img src="https://ui-avatars.com/api/?name=J+D&background=random"
                                    class="rounded-circle opacity-50" width="28" height="28" alt="Team">
                                <div class="text-muted small">
                                    <i class="bi bi-chat-dots me-1"></i> 2
                                    <i class="bi bi-paperclip ms-2 me-1"></i> 0
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-secondary w-100 border-dashed"><i class="bi bi-plus"></i> Add
                    Task</button>
            </div>
        </div>

    </div>
@endsection
