@php
    $activeTab = request('tab', 'profile');
@endphp

<div class="tab-pane fade {{ $activeTab === 'mail' ? 'show active' : '' }}" id="v-pills-mail" role="tabpanel">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h5 class="card-title fw-bold mb-4">{{ _trans('common.Mail & SMTP Configuration') }}</h5>

            <form method="POST" action="{{ route('settings.mail') }}" class="needs-validation">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="mail_mailer" class="form-label fw-semibold small">{{ _trans('common.Mail Driver') }} <span class="text-danger">*</span></label>
                        <select name="mail_mailer" id="mail_mailer" class="form-select @error('mail_mailer') is-invalid @enderror" required>
                            @php $currentMailer = old('mail_mailer', $settings['mail_mailer'] ?? 'smtp'); @endphp
                            <option value="smtp" {{ $currentMailer === 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="log" {{ $currentMailer === 'log' ? 'selected' : '' }}>Log (Local Testing)</option>
                            <option value="sendmail" {{ $currentMailer === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                        </select>
                        @error('mail_mailer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="mail_host" class="form-label fw-semibold small">{{ _trans('common.SMTP Host') }}</label>
                        <input type="text"
                            name="mail_host"
                            id="mail_host"
                            class="form-control @error('mail_host') is-invalid @enderror"
                            value="{{ old('mail_host', $settings['mail_host'] ?? 'smtp.mailtrap.io') }}"
                            placeholder="smtp.mailtrap.io">
                        @error('mail_host')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="mail_port" class="form-label fw-semibold small">{{ _trans('common.SMTP Port') }}</label>
                        <input type="number"
                            name="mail_port"
                            id="mail_port"
                            class="form-control @error('mail_port') is-invalid @enderror"
                            value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}"
                            placeholder="587">
                        @error('mail_port')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="mail_username" class="form-label fw-semibold small">{{ _trans('common.SMTP Username') }}</label>
                        <input type="text"
                            name="mail_username"
                            id="mail_username"
                            class="form-control @error('mail_username') is-invalid @enderror"
                            value="{{ old('mail_username', $settings['mail_username'] ?? '') }}"
                            placeholder="username">
                        @error('mail_username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="mail_password" class="form-label fw-semibold small">{{ _trans('common.SMTP Password') }}</label>
                        <input type="password"
                            name="mail_password"
                            id="mail_password"
                            class="form-control @error('mail_password') is-invalid @enderror"
                            value="{{ old('mail_password', $settings['mail_password'] ?? '') }}"
                            placeholder="••••••••">
                        @error('mail_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="mail_encryption" class="form-label fw-semibold small">{{ _trans('common.Encryption') }}</label>
                        <select name="mail_encryption" id="mail_encryption" class="form-select @error('mail_encryption') is-invalid @enderror">
                            @php $currentEncryption = old('mail_encryption', $settings['mail_encryption'] ?? 'tls'); @endphp
                            <option value="tls" {{ $currentEncryption === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ $currentEncryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="null" {{ $currentEncryption === 'null' ? 'selected' : '' }}>None</option>
                        </select>
                        @error('mail_encryption')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="mail_from_address" class="form-label fw-semibold small">{{ _trans('common.From Email Address') }}</label>
                        <input type="email"
                            name="mail_from_address"
                            id="mail_from_address"
                            class="form-control @error('mail_from_address') is-invalid @enderror"
                            value="{{ old('mail_from_address', $settings['mail_from_address'] ?? 'noreply@erp.test') }}"
                            placeholder="noreply@erp.test">
                        @error('mail_from_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="mail_from_name" class="form-label fw-semibold small">{{ _trans('common.From Name') }}</label>
                        <input type="text"
                            name="mail_from_name"
                            id="mail_from_name"
                            class="form-control @error('mail_from_name') is-invalid @enderror"
                            value="{{ old('mail_from_name', $settings['mail_from_name'] ?? 'ERP Pro') }}"
                            placeholder="ERP Pro">
                        @error('mail_from_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Save Mail Configuration') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
