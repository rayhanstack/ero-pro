@extends('admin.layouts.app')
@section('title', $title)

@section('content')
    {{-- Page Header Component --}}
    <x-ui.page-header
        title="UI Components Showcase"
        subtitle="Explore all available design system components, forms, tables, and modals"
        :breadcrumbs="[['url' => route('dashboard'), 'label' => 'Dashboard'], ['label' => 'UI Components']]">
        <x-slot:actions>
            <button type="button" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-download me-1"></i> {{ _trans('common.Export') }}
            </button>
            <x-ui.button variant="primary" size="sm">
                <i class="bi bi-plus-lg me-1"></i> {{ _trans('common.Create New') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Form Components Row --}}
    <div class="row g-4 mb-4">
        <!-- Forms and Inputs Section -->
        <div class="col-xl-6">
            <x-ui.card title="Standard Form Elements" subtitle="Basic inputs, textareas, and image uploads" icon="bi-input-cursor-text">
                <form>
                    <div class="row g-3">
                        <x-form.image-upload name="company_logo" label="Company Logo" columns="col-md-6" />
                        <x-form.image-upload name="favicon" label="Favicon" columns="col-md-6" />

                        <x-form.input name="company_name" label="Company Name" placeholder="Enter company name" columns="col-md-6" required />
                        <x-form.input name="company_email" label="Company Email" type="email" placeholder="Enter company email" columns="col-md-6" required />

                        <x-form.input name="company_phone" label="Company Phone" type="tel" placeholder="Enter company phone" columns="col-md-6" />
                        <x-form.input name="company_description" label="Short Description" placeholder="Enter brief description" columns="col-md-6" />

                        <div class="col-12">
                            <x-form.textarea name="company_address" label="Company Address" placeholder="Enter company address" rows="3" />
                        </div>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- Advanced Inputs: Date Pickers & Select2 -->
        <div class="col-xl-6">
            <x-ui.card title="Advanced Inputs & Pickers" subtitle="Flatpickr date pickers and Select2 dropdowns" icon="bi-sliders">
                <form>
                    <div class="row g-3">
                        {{-- Flatpickr Date Component --}}
                        <x-form.date name="start_date" label="Standard Date Picker" placeholder="Pick a date" columns="col-md-6" />
                        <x-form.date name="event_datetime" label="Date & Time Picker" placeholder="Pick date & time" enableTime columns="col-md-6" />
                        <x-form.date name="date_range" label="Date Range Picker" placeholder="Select date range" mode="range" columns="col-12" />

                        {{-- Select2 Component --}}
                        <x-form.select2
                            name="country"
                            label="Select2 Single (Searchable)"
                            placeholder="Choose country..."
                            columns="col-md-6"
                            :options="['us' => 'United States', 'uk' => 'United Kingdom', 'ca' => 'Canada', 'au' => 'Australia', 'de' => 'Germany']" />

                        <x-form.select2
                            name="skills"
                            label="Select2 Multi-Select"
                            placeholder="Choose skills..."
                            multiple
                            columns="col-md-6"
                            :options="['php' => 'PHP / Laravel', 'js' => 'JavaScript', 'vue' => 'Vue.js', 'react' => 'React', 'mysql' => 'MySQL']" />

                        <div class="col-12"><hr class="my-2"></div>

                        <h6 class="fw-bold mb-2">Checkboxes, Radios & Switches</h6>
                        <div class="col-md-6">
                            <x-form.checkbox id="flexCheckDefault" label="Default checkbox" class="mb-2" />
                            <x-form.checkbox id="flexCheckChecked" label="Checked checkbox" class="mb-2" checked />
                        </div>
                        <div class="col-md-6">
                            <x-form.switch id="flexSwitchCheckDefault" label="Default switch input" class="mb-2" />
                            <x-form.switch id="flexSwitchCheckChecked" label="Checked switch input" checked />
                        </div>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>

    <!-- Buttons & Badges Row -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <x-ui.card title="Buttons, Badges & Modals" subtitle="Standard button variants, badges, and delete confirmation modal" icon="bi-app-indicator">
                <div class="mb-4 d-flex gap-2 flex-wrap">
                    <x-ui.button variant="primary">Primary</x-ui.button>
                    <x-ui.button variant="secondary">Secondary</x-ui.button>
                    <x-ui.button variant="success">Success</x-ui.button>
                    <x-ui.button variant="danger">Danger</x-ui.button>
                    <x-ui.button variant="warning">Warning</x-ui.button>
                    <x-ui.button variant="info">Info</x-ui.button>
                    <x-ui.button variant="light">Light</x-ui.button>
                    <x-ui.button variant="dark">Dark</x-ui.button>
                    <x-ui.button variant="link">Link</x-ui.button>
                    <x-ui.button variant="primary" size="lg">Large Button</x-ui.button>
                    <x-ui.button variant="danger" outline>Outline Danger</x-ui.button>

                    <!-- Trigger Delete Confirmation Modal -->
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#demoConfirmDeleteModal" data-action="/admin/demo/1" data-message="Are you sure you want to delete demo item #1?">
                        <i class="bi bi-trash me-1"></i> Trigger Delete Modal
                    </button>
                </div>

                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <x-ui.badge variant="primary">Primary</x-ui.badge>
                    <x-ui.badge variant="secondary">Secondary</x-ui.badge>
                    <x-ui.badge variant="success">Success</x-ui.badge>
                    <x-ui.badge variant="danger">Danger</x-ui.badge>
                    <x-ui.badge variant="warning">Warning</x-ui.badge>
                    <x-ui.badge variant="info">Info</x-ui.badge>
                    <x-ui.badge variant="light">Light</x-ui.badge>
                    <x-ui.badge variant="dark">Dark</x-ui.badge>
                    <x-ui.badge variant="primary" pill>Pill Primary</x-ui.badge>
                    <x-ui.badge variant="success" pill>Pill Success</x-ui.badge>
                </div>
            </x-ui.card>
        </div>
    </div>

    <!-- UI Table Component Examples -->
    <div class="row g-4 mb-4">
        <!-- Populated Table -->
        <div class="col-xl-6">
            <x-ui.card title="Data Table Component" subtitle="Standard responsive table with actions and pagination" icon="bi-table">
                <x-slot:actions>
                    <x-ui.button variant="primary" size="sm">
                        <i class="bi bi-plus-lg me-1"></i> Add Record
                    </x-ui.button>
                </x-slot:actions>

                <x-ui.table :headers="['User', 'Role', 'Status', 'Action']">
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=Jane+Doe&background=4f46e5&color=fff" class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                <div>
                                    <div class="fw-bold">Jane Doe</div>
                                    <div class="text-muted small">jane@example.com</div>
                                </div>
                            </div>
                        </td>
                        <td>Staff</td>
                        <td><span class="badge bg-success rounded-pill">Active</span></td>
                        <td>
                            <button class="btn btn-sm btn-light text-primary me-1"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light text-danger" data-bs-toggle="modal" data-bs-target="#demoConfirmDeleteModal" data-action="/demo/delete/1001"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=John+Smith&background=0284c7&color=fff" class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                <div>
                                    <div class="fw-bold">John Smith</div>
                                    <div class="text-muted small">john@example.com</div>
                                </div>
                            </div>
                        </td>
                        <td>Manager</td>
                        <td><span class="badge bg-warning text-dark rounded-pill">Pending</span></td>
                        <td>
                            <button class="btn btn-sm btn-light text-primary me-1"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light text-danger" data-bs-toggle="modal" data-bs-target="#demoConfirmDeleteModal" data-action="/demo/delete/1002"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                </x-ui.table>

                <x-ui.pagination />
            </x-ui.card>
        </div>

        <!-- Empty State Table -->
        <div class="col-xl-6">
            <x-ui.card title="Empty State Table" subtitle="Component rendering when no items or records exist" icon="bi-inbox">
                <x-ui.table
                    :headers="['Item', 'Category', 'Price', 'Action']"
                    :empty="true"
                    emptyMessage="No Products Available"
                    emptySubtitle="Click the button below to add your first product."
                    emptyIcon="bi-box-seam" />
            </x-ui.card>
        </div>
    </div>
@endsection

@push('modals')
    {{-- Confirm Delete Modal Component Demonstration --}}
    <x-ui.confirm-delete id="demoConfirmDeleteModal" title="Delete Confirmation" message="Are you sure you want to delete this record? This action cannot be undone." />
@endpush
