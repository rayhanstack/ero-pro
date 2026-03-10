@extends('admin.layouts.app')
@section('title', $title)
@section('content')

    <div class="row g-4 mb-4">
        <!-- Forms and Inputs Section -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">Form Inputs & Elements</h5>

                    <form>
                        <x-form.image-upload name="company_logo" label="Company Logo" value="{{ @$settings->company_logo }}"
                            required />

                        <x-form.image-upload name="favicon" label="Favicon" value="{{ @$settings->favicon }}" />


                        <x-form.input name="company_name" label="Company Name" placeholder="Enter company name"
                            value="{{ @$settings->company_name }}" required />

                        <x-form.input name="company_email" label="Company Email" type="email"
                            placeholder="Enter company email" value="{{ @$settings->company_email }}" required />

                        <x-form.input name="company_phone" label="Company Phone" type="number"
                            placeholder="Enter company phone" value="{{ @$settings->company_phone }}" />

                        <x-form.input name="company_description" label="Company Description"
                            placeholder="Enter company description" value="{{ @$settings->company_description }}"
                            required />

                        <x-form.textarea name="company_address" label="Company Address" placeholder="Enter company address"
                            value="{{ @$settings->company_address }}" required />

                        <x-form.select id="exampleSelect" label="Select2 Dropdown" class="select2" :options="['1' => 'One', '2' => 'Two', '3' => 'Three', '4' => 'Four', '5' => 'Five']" />
                    </form>
                </div>
            </div>
        </div>

        <!-- Advanced Inputs & Image Upload -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">Advanced Components & Uploads</h5>

                    <form>
                        <x-form.image-upload id="formFilePreview" />

                        <x-form.input id="formFileMultiple" type="file" label="Multiple File Upload" multiple />

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3">Checkboxes & Radios</h6>

                        <x-form.checkbox id="flexCheckDefault" label="Default checkbox" class="mb-2" />
                        <x-form.checkbox id="flexCheckChecked" label="Checked checkbox" class="mb-2" checked />

                        <div class="mt-3">
                            <x-form.radio id="flexRadioDefault1" name="flexRadioDefault" label="Default radio"
                                class="mb-2" />
                            <x-form.radio id="flexRadioDefault2" name="flexRadioDefault" label="Default checked radio"
                                class="mb-3" checked />
                        </div>

                        <h6 class="fw-bold mb-3">Switches</h6>
                        <x-form.switch id="flexSwitchCheckDefault" label="Default switch input" class="mb-2" />
                        <x-form.switch id="flexSwitchCheckChecked" label="Checked switch input" checked />
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Buttons & Badges Row -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">Buttons & Badges</h5>

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

                        <x-ui.button variant="primary" size="lg">
                            Save Changes
                        </x-ui.button>

                        <x-ui.button variant="danger" outline>
                            Delete
                        </x-ui.button>

                        <x-ui.button variant="success" block>
                            Submit
                        </x-ui.button>
                    </div>

                    <div class="mb-2 d-flex gap-2 flex-wrap align-items-center">
                        <x-ui.badge variant="primary">Primary</x-ui.badge>

                        <x-ui.badge variant="secondary">Secondary</x-ui.badge>

                        <x-ui.badge variant="success">Success</x-ui.badge>

                        <x-ui.badge variant="danger">Danger</x-ui.badge>

                        <x-ui.badge variant="warning">Warning</x-ui.badge>

                        <x-ui.badge variant="info">Info</x-ui.badge>

                        <x-ui.badge variant="light">Light</x-ui.badge>

                        <x-ui.badge variant="dark">Dark</x-ui.badge>

                        <x-ui.badge variant="primary" pill>
                            Pill Badge
                        </x-ui.badge>

                        <x-ui.badge variant="success" pill>
                            Pill Badge
                        </x-ui.badge>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table Example -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="card-title fw-bold mb-0">Data Table Example</h5>
                            <button class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i> Add New
                                Record</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value=""
                                                    id="selectAll">
                                            </div>
                                        </th>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                        <th class="pe-4 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="">
                                            </div>
                                        </td>
                                        <td class="text-muted fw-bold">#1001</td>
                                        <td>
                                            <img src="https://ui-avatars.com/api/?name=Jane+Doe&background=random"
                                                class="rounded" width="40" height="40" alt="Avatar">
                                        </td>
                                        <td class="fw-bold">Jane Doe</td>
                                        <td>Staff</td>
                                        <td><span class="badge bg-success rounded-pill">Active</span></td>
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
                                            <button class="btn btn-sm btn-light text-primary me-1"><i
                                                    class="bi bi-eye"></i></button>
                                            <button class="btn btn-sm btn-light text-secondary me-1"><i
                                                    class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-light text-danger"><i
                                                    class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="">
                                            </div>
                                        </td>
                                        <td class="text-muted fw-bold">#1002</td>
                                        <td>
                                            <img src="https://ui-avatars.com/api/?name=John+Smith&background=random"
                                                class="rounded" width="40" height="40" alt="Avatar">
                                        </td>
                                        <td class="fw-bold">John Smith</td>
                                        <td>Manager</td>
                                        <td><span class="badge bg-warning text-dark rounded-pill">Pending</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2 small">45%</span>
                                                <div class="progress" style="height: 6px; width: 60px;">
                                                    <div class="progress-bar bg-warning" role="progressbar"
                                                        style="width: 45%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <button class="btn btn-sm btn-light text-primary me-1"><i
                                                    class="bi bi-eye"></i></button>
                                            <button class="btn btn-sm btn-light text-secondary me-1"><i
                                                    class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-light text-danger"><i
                                                    class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="">
                                            </div>
                                        </td>
                                        <td class="text-muted fw-bold">#1003</td>
                                        <td>
                                            <div class="bg-light d-flex align-items-center justify-content-center rounded text-muted"
                                                style="width: 40px; height: 40px;">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        </td>
                                        <td class="fw-bold">Mike Ross</td>
                                        <td>Client</td>
                                        <td><span class="badge bg-danger rounded-pill">Inactive</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2 small">15%</span>
                                                <div class="progress" style="height: 6px; width: 60px;">
                                                    <div class="progress-bar bg-danger" role="progressbar"
                                                        style="width: 15%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <button class="btn btn-sm btn-light text-primary me-1"><i
                                                    class="bi bi-eye"></i></button>
                                            <button class="btn btn-sm btn-light text-secondary me-1"><i
                                                    class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-light text-danger"><i
                                                    class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('script')
        <script>
            // Initialize Select2
            $(document).ready(function() {
                $('.select2').select2({
                    theme: "classic",
                    width: '100%',
                    placeholder: "Select an option"
                });
            });
        </script>
    @endpush
