@props([
    'id' => 'confirmDeleteModal',
    'title' => 'Confirm Deletion',
    'message' => 'Are you sure you want to delete this item? This action cannot be undone.',
    'confirmText' => 'Delete',
    'cancelText' => 'Cancel',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 text-danger mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-trash3-fill fs-3"></i>
                </div>
                <h5 class="modal-title fw-bold text-dark mb-2" id="{{ $id }}Label">{{ _trans($title) }}</h5>
                <p class="text-muted small mb-0 delete-modal-message">{{ _trans($message) }}</p>
            </div>
            <div class="modal-footer border-0 p-3 bg-light bg-opacity-50 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary flex-grow-1" data-bs-dismiss="modal">
                    {{ _trans($cancelText) }}
                </button>
                <form id="{{ $id }}Form" method="POST" action="" class="flex-grow-1 m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        {{ _trans($confirmText) }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@pushonce('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var confirmModalEl = document.getElementById('{{ $id }}');
            if (confirmModalEl) {
                confirmModalEl.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    if (button) {
                        var action = button.getAttribute('data-action') || button.getAttribute('data-url');
                        var form = confirmModalEl.querySelector('#{{ $id }}Form');
                        if (form && action) {
                            form.setAttribute('action', action);
                        }
                        var customMessage = button.getAttribute('data-message');
                        if (customMessage) {
                            var msgEl = confirmModalEl.querySelector('.delete-modal-message');
                            if (msgEl) msgEl.textContent = customMessage;
                        }
                    }
                });
            }
        });
    </script>
@endpushonce
