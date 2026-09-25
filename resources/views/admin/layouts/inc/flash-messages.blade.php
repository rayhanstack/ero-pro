<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;">
    {{-- Success Message --}}
    @if (session('success'))
        <div class="toast align-items-center text-bg-success border-0 shadow-lg mb-2 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif

    {{-- Error Message --}}
    @if (session('error'))
        <div class="toast align-items-center text-bg-danger border-0 shadow-lg mb-2 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif

    {{-- Warning Message --}}
    @if (session('warning'))
        <div class="toast align-items-center text-bg-warning border-0 shadow-lg mb-2 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center text-dark">
                    <i class="bi bi-exclamation-circle-fill fs-5 me-2"></i>
                    <span>{{ session('warning') }}</span>
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif

    {{-- Info Message --}}
    @if (session('info'))
        <div class="toast align-items-center text-bg-info border-0 shadow-lg mb-2 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center text-white">
                    <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                    <span>{{ session('info') }}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if (isset($errors) && $errors->any())
        <div class="toast align-items-center text-bg-danger border-0 shadow-lg mb-2 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="6000">
            <div class="d-flex">
                <div class="toast-body">
                    <div class="d-flex align-items-center mb-1 fw-bold">
                        <i class="bi bi-shield-exclamation fs-5 me-2"></i>
                        <span>{{ _trans('common.Please correct the errors below') }}</span>
                    </div>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif
</div>

@pushonce('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toastElList = [].slice.call(document.querySelectorAll('.toast-container .toast'));
            var toastList = toastElList.map(function(toastEl) {
                var toast = new bootstrap.Toast(toastEl, {
                    autohide: true
                });
                toast.show();
                return toast;
            });
        });
    </script>
@endpushonce
