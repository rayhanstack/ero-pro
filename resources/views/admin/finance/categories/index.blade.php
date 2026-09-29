@extends('admin.layouts.app')
@section('title', $title ?? _trans('common.Finance Categories'))

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ _trans('common.Finance Categories') }}</h3>
            <p class="text-muted small mb-0">{{ _trans('common.Organize and classify income streams and expenditure categories') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @can('finance.create')
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ _trans('common.New Category') }}</span>
                </button>
            @endcan
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills mb-4 gap-2" id="categoryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active px-4 py-2 fw-semibold rounded-pill d-flex align-items-center gap-2" id="income-tab" data-bs-toggle="pill" data-bs-target="#income-pane" type="button" role="tab" aria-controls="income-pane" aria-selected="true">
                <i class="bi bi-arrow-down-left-circle text-success fs-5"></i>
                <span>{{ _trans('common.Income Categories') }}</span>
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1">{{ $incomeCategories->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2 fw-semibold rounded-pill d-flex align-items-center gap-2" id="expense-tab" data-bs-toggle="pill" data-bs-target="#expense-pane" type="button" role="tab" aria-controls="expense-pane" aria-selected="false">
                <i class="bi bi-arrow-up-right-circle text-danger fs-5"></i>
                <span>{{ _trans('common.Expense Categories') }}</span>
                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1">{{ $expenseCategories->count() }}</span>
            </button>
        </li>
    </ul>

    {{-- Tabs Content --}}
    <div class="tab-content" id="categoryTabsContent">
        {{-- Income Categories Pane --}}
        <div class="tab-pane fade show active" id="income-pane" role="tabpanel" aria-labelledby="income-tab">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ _trans('common.Income Categories List') }}</h5>
                        <small class="text-muted">{{ _trans('common.Categories used to categorize revenue and invoices') }}</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted extra-small text-uppercase">
                                <tr>
                                    <th class="ps-3">{{ _trans('common.Category Name') }}</th>
                                    <th>{{ _trans('common.Description') }}</th>
                                    <th class="text-center">{{ _trans('common.Transactions Count') }}</th>
                                    <th class="text-end pe-3">{{ _trans('common.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incomeCategories as $cat)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                    <i class="bi bi-tag-fill fs-6"></i>
                                                </div>
                                                <span class="fw-bold text-dark">{{ $cat->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ $cat->description ?: '—' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-medium">
                                                {{ $cat->transactions_count ?? 0 }} {{ _trans('common.records') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-icon btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                                    @can('finance.edit')
                                                        <li>
                                                            <a class="dropdown-item py-2 d-flex align-items-center gap-2 edit-category-btn"
                                                               href="javascript:void(0)"
                                                               data-id="{{ $cat->id }}"
                                                               data-type="income"
                                                               data-name="{{ $cat->name }}"
                                                               data-description="{{ $cat->description }}">
                                                                <i class="bi bi-pencil text-warning"></i>
                                                                <span>{{ _trans('common.Edit') }}</span>
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can('finance.delete')
                                                        <li>
                                                            <form action="{{ route('finance.categories.destroy', [$cat->id, 'type' => 'income']) }}" method="POST" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this category?') }}')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger">
                                                                    <i class="bi bi-trash"></i>
                                                                    <span>{{ _trans('common.Delete') }}</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-tags display-6 d-block text-muted opacity-50 mb-2"></i>
                                            {{ _trans('common.No income categories found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Expense Categories Pane --}}
        <div class="tab-pane fade" id="expense-pane" role="tabpanel" aria-labelledby="expense-tab">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ _trans('common.Expense Categories List') }}</h5>
                        <small class="text-muted">{{ _trans('common.Categories used to track company expenses and payroll') }}</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted extra-small text-uppercase">
                                <tr>
                                    <th class="ps-3">{{ _trans('common.Category Name') }}</th>
                                    <th>{{ _trans('common.Description') }}</th>
                                    <th class="text-center">{{ _trans('common.Transactions Count') }}</th>
                                    <th class="text-end pe-3">{{ _trans('common.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenseCategories as $cat)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                    <i class="bi bi-tag-fill fs-6"></i>
                                                </div>
                                                <span class="fw-bold text-dark">{{ $cat->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ $cat->description ?: '—' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-medium">
                                                {{ $cat->transactions_count ?? 0 }} {{ _trans('common.records') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-icon btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                                    @can('finance.edit')
                                                        <li>
                                                            <a class="dropdown-item py-2 d-flex align-items-center gap-2 edit-category-btn"
                                                               href="javascript:void(0)"
                                                               data-id="{{ $cat->id }}"
                                                               data-type="expense"
                                                               data-name="{{ $cat->name }}"
                                                               data-description="{{ $cat->description }}">
                                                                <i class="bi bi-pencil text-warning"></i>
                                                                <span>{{ _trans('common.Edit') }}</span>
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can('finance.delete')
                                                        <li>
                                                            <form action="{{ route('finance.categories.destroy', [$cat->id, 'type' => 'expense']) }}" method="POST" onsubmit="return confirm('{{ _trans('common.Are you sure you want to delete this category?') }}')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger">
                                                                    <i class="bi bi-trash"></i>
                                                                    <span>{{ _trans('common.Delete') }}</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-tags display-6 d-block text-muted opacity-50 mb-2"></i>
                                            {{ _trans('common.No expense categories found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Category Modal --}}
    <div class="modal fade" id="newCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form action="{{ route('finance.categories.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold text-dark">{{ _trans('common.Create Finance Category') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ _trans('common.Category Type') }} <span class="text-danger">*</span></label>
                            <select name="type" class="form-select rounded-3" required>
                                <option value="income">{{ _trans('common.Income Category') }}</option>
                                <option value="expense">{{ _trans('common.Expense Category') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ _trans('common.Category Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="{{ _trans('common.e.g., Consulting Fees, Office Supplies') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ _trans('common.Description') }}</label>
                            <textarea name="description" class="form-control rounded-3" rows="3" placeholder="{{ _trans('common.Optional description or details') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4">{{ _trans('common.Create Category') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Category Modal --}}
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form id="editCategoryForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="type" id="edit_category_type">
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold text-dark">{{ _trans('common.Edit Finance Category') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ _trans('common.Category Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_category_name" class="form-control rounded-3" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">{{ _trans('common.Description') }}</label>
                            <textarea name="description" id="edit_category_description" class="form-control rounded-3" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">{{ _trans('common.Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4">{{ _trans('common.Update Category') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));

        $(document).on('click', '.edit-category-btn', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');
            const name = $(this).data('name');
            const description = $(this).data('description');

            let updateUrl = "{{ route('finance.categories.update', ':id') }}";
            updateUrl = updateUrl.replace(':id', id);

            $('#editCategoryForm').attr('action', updateUrl);
            $('#edit_category_type').val(type);
            $('#edit_category_name').val(name);
            $('#edit_category_description').val(description || '');

            editModal.show();
        });
    });
</script>
@endpush
