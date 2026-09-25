@props([
    'groupedPermissions' => [],
    'rolePermissions' => [],
])

<div class="permission-matrix-container">
    <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-light rounded-3 border">
        <div>
            <h6 class="fw-bold mb-1 text-dark">{{ _trans('common.Module Permissions') }}</h6>
            <p class="text-muted small mb-0">{{ _trans('common.Configure access control permissions for this role') }}</p>
        </div>
        <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
            <input class="form-check-input fs-5 cursor-pointer m-0" type="checkbox" id="selectAllGlobal">
            <label class="form-check-label fw-bold small text-dark cursor-pointer" for="selectAllGlobal">
                {{ _trans('common.Select All Permissions') }}
            </label>
        </div>
    </div>

    <div class="row g-3">
        @foreach ($groupedPermissions as $module => $permissions)
            <div class="col-12 col-lg-6">
                <div class="card h-100 border rounded-3 shadow-none permission-module-card">
                    <div class="card-header bg-light py-2.5 px-3 d-flex align-items-center justify-content-between border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock text-primary"></i>
                            <span class="fw-bold text-dark text-capitalize">{{ _trans('common.' . ucfirst($module)) }}</span>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input cursor-pointer select-all-module" type="checkbox" id="module_{{ $module }}" data-module="{{ $module }}">
                            <label class="form-check-label small text-muted cursor-pointer" for="module_{{ $module }}">
                                {{ _trans('common.All') }}
                            </label>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            @foreach ($permissions as $permission)
                                @php
                                    $action = explode('.', $permission->name)[1] ?? $permission->name;
                                    $isChecked = in_array($permission->name, (array) old('permissions', $rolePermissions));
                                @endphp
                                <div class="col-6">
                                    <div class="form-check form-check-inline m-0 p-2 rounded-2 border border-dashed hover-bg-light w-100 d-flex align-items-center">
                                        <input class="form-check-input me-2 cursor-pointer perm-checkbox module-item-{{ $module }}"
                                            type="checkbox"
                                            name="permissions[]"
                                            id="perm_{{ $permission->id }}"
                                            value="{{ $permission->name }}"
                                            {{ $isChecked ? 'checked' : '' }}
                                            data-module="{{ $module }}">
                                        <label class="form-check-label small text-dark cursor-pointer text-capitalize text-truncate" for="perm_{{ $permission->id }}">
                                            {{ _trans('common.' . ucfirst($action)) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAllGlobal = document.getElementById('selectAllGlobal');
        const moduleCheckboxes = document.querySelectorAll('.select-all-module');
        const permCheckboxes = document.querySelectorAll('.perm-checkbox');

        // Function to update module 'select all' state
        function updateModuleState(module) {
            const moduleItems = document.querySelectorAll(`.module-item-${module}`);
            const moduleMaster = document.querySelector(`.select-all-module[data-module="${module}"]`);
            if (!moduleMaster || moduleItems.length === 0) return;

            const allChecked = Array.from(moduleItems).every(cb => cb.checked);
            moduleMaster.checked = allChecked;
        }

        // Function to update global state
        function updateGlobalState() {
            if (!selectAllGlobal) return;
            const allChecked = Array.from(permCheckboxes).every(cb => cb.checked);
            selectAllGlobal.checked = allChecked;
        }

        // Initial sync
        const uniqueModules = new Set(Array.from(permCheckboxes).map(cb => cb.dataset.module));
        uniqueModules.forEach(updateModuleState);
        updateGlobalState();

        // Global Select All handler
        if (selectAllGlobal) {
            selectAllGlobal.addEventListener('change', function () {
                const checked = this.checked;
                permCheckboxes.forEach(cb => cb.checked = checked);
                moduleCheckboxes.forEach(cb => cb.checked = checked);
            });
        }

        // Module Select All handler
        moduleCheckboxes.forEach(mcb => {
            mcb.addEventListener('change', function () {
                const module = this.dataset.module;
                const checked = this.checked;
                const items = document.querySelectorAll(`.module-item-${module}`);
                items.forEach(cb => cb.checked = checked);
                updateGlobalState();
            });
        });

        // Single checkbox change handler
        permCheckboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                updateModuleState(this.dataset.module);
                updateGlobalState();
            });
        });
    });
</script>
@endpush
