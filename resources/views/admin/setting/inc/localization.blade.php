@php
    $activeTab = request('tab', 'profile');
@endphp

<div class="tab-pane fade {{ $activeTab === 'localization' ? 'show active' : '' }}" id="v-pills-localization" role="tabpanel">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="card-title fw-bold mb-0">{{ _trans('common.Localization & Formats') }}</h5>
                <a href="{{ route('languages.index') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-translate"></i>
                    <span>{{ _trans('common.Manage Languages') }}</span>
                </a>
            </div>

            <form method="POST" action="{{ route('settings.localization') }}" class="needs-validation">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="timezone" class="form-label fw-semibold small">{{ _trans('common.Default Timezone') }} <span class="text-danger">*</span></label>
                        <select name="timezone" id="timezone" class="form-select @error('timezone') is-invalid @enderror" required>
                            @php $currentTimezone = old('timezone', $settings['timezone'] ?? config('app.timezone', 'UTC')); @endphp
                            <option value="UTC" {{ $currentTimezone === 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                            <option value="Asia/Dhaka" {{ $currentTimezone === 'Asia/Dhaka' ? 'selected' : '' }}>Asia/Dhaka (GMT+6)</option>
                            <option value="America/New_York" {{ $currentTimezone === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST/EDT)</option>
                            <option value="Europe/London" {{ $currentTimezone === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT/BST)</option>
                            <option value="Asia/Dubai" {{ $currentTimezone === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)</option>
                            <option value="Asia/Kolkata" {{ $currentTimezone === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                            <option value="Asia/Tokyo" {{ $currentTimezone === 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (JST)</option>
                        </select>
                        @error('timezone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="date_format" class="form-label fw-semibold small">{{ _trans('common.Date Format') }} <span class="text-danger">*</span></label>
                        <select name="date_format" id="date_format" class="form-select @error('date_format') is-invalid @enderror" required>
                            @php $currentDateFormat = old('date_format', $settings['date_format'] ?? 'Y-m-d'); @endphp
                            <option value="Y-m-d" {{ $currentDateFormat === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD ({{ date('Y-m-d') }})</option>
                            <option value="d-m-Y" {{ $currentDateFormat === 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY ({{ date('d-m-Y') }})</option>
                            <option value="d/m/Y" {{ $currentDateFormat === 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY ({{ date('d/m/Y') }})</option>
                            <option value="m/d/Y" {{ $currentDateFormat === 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY ({{ date('m/d/Y') }})</option>
                            <option value="d M Y" {{ $currentDateFormat === 'd M Y' ? 'selected' : '' }}>DD Mon YYYY ({{ date('d M Y') }})</option>
                        </select>
                        @error('date_format')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="time_format" class="form-label fw-semibold small">{{ _trans('common.Time Format') }} <span class="text-danger">*</span></label>
                        <select name="time_format" id="time_format" class="form-select @error('time_format') is-invalid @enderror" required>
                            @php $currentTimeFormat = old('time_format', $settings['time_format'] ?? 'H:i:s'); @endphp
                            <option value="H:i:s" {{ $currentTimeFormat === 'H:i:s' ? 'selected' : '' }}>24 Hours with Seconds ({{ date('H:i:s') }})</option>
                            <option value="H:i" {{ $currentTimeFormat === 'H:i' ? 'selected' : '' }}>24 Hours ({{ date('H:i') }})</option>
                            <option value="h:i A" {{ $currentTimeFormat === 'h:i A' ? 'selected' : '' }}>12 Hours with AM/PM ({{ date('h:i A') }})</option>
                            <option value="h:i a" {{ $currentTimeFormat === 'h:i a' ? 'selected' : '' }}>12 Hours with am/pm ({{ date('h:i a') }})</option>
                        </select>
                        @error('time_format')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="default_currency" class="form-label fw-semibold small">{{ _trans('common.Default Currency') }} <span class="text-danger">*</span></label>
                        <select name="default_currency" id="default_currency" class="form-select @error('default_currency') is-invalid @enderror" required>
                            @php $currentCurrency = old('default_currency', $settings['default_currency'] ?? 'BDT'); @endphp
                            @foreach ($currencies as $cur)
                                <option value="{{ $cur->code }}" {{ $currentCurrency === $cur->code ? 'selected' : '' }}>
                                    {{ $cur->code }} ({{ $cur->symbol }} - {{ $cur->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('default_currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="default_language" class="form-label fw-semibold small">{{ _trans('common.Default Language') }} <span class="text-danger">*</span></label>
                        <select name="default_language" id="default_language" class="form-select @error('default_language') is-invalid @enderror" required>
                            @php $currentLang = old('default_language', $settings['default_language'] ?? 'en'); @endphp
                            @foreach ($languages as $lng)
                                <option value="{{ $lng->code }}" {{ $currentLang === $lng->code ? 'selected' : '' }}>
                                    {{ $lng->name }} ({{ $lng->native }})
                                </option>
                            @endforeach
                        </select>
                        @error('default_language')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="week_start_day" class="form-label fw-semibold small">{{ _trans('common.Week Start Day') }} <span class="text-danger">*</span></label>
                        <select name="week_start_day" id="week_start_day" class="form-select @error('week_start_day') is-invalid @enderror" required>
                            @php $currentWeekStart = old('week_start_day', $settings['week_start_day'] ?? 'sunday'); @endphp
                            <option value="sunday" {{ $currentWeekStart === 'sunday' ? 'selected' : '' }}>{{ _trans('common.Sunday') }}</option>
                            <option value="monday" {{ $currentWeekStart === 'monday' ? 'selected' : '' }}>{{ _trans('common.Monday') }}</option>
                            <option value="saturday" {{ $currentWeekStart === 'saturday' ? 'selected' : '' }}>{{ _trans('common.Saturday') }}</option>
                        </select>
                        @error('week_start_day')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check-lg"></i>
                            <span>{{ _trans('common.Save Localization') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
