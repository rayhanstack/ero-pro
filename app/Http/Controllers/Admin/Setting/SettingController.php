<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Helpers\MediaHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateAttendanceSettingRequest;
use App\Http\Requests\Setting\UpdateCompanySettingRequest;
use App\Http\Requests\Setting\UpdateLeaveSettingRequest;
use App\Http\Requests\Setting\UpdateLocalizationSettingRequest;
use App\Http\Requests\Setting\UpdateMailSettingRequest;
use App\Http\Requests\Setting\UpdatePayrollSettingRequest;
use App\Models\Currency;
use App\Models\Language;
use App\Services\Setting\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Display settings page with all configured tabs.
     */
    public function index(Request $request): View
    {
        $settings = $this->settingService->all();
        $currencies = Currency::where('status', 'active')->orderBy('code', 'asc')->get();
        $languages = Language::where('status', 'active')->orderBy('name', 'asc')->get();
        $title = _trans('common.Settings');

        return view('admin.setting.index', compact('settings', 'currencies', 'languages', 'title'));
    }

    /**
     * Update company details and branding.
     */
    public function updateCompany(UpdateCompanySettingRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $data = [
            'company_name' => $validated['company_name'],
            'company_email' => $validated['company_email'] ?? '',
            'company_phone' => $validated['company_phone'] ?? '',
            'company_address' => $validated['company_address'] ?? '',
            'company_description' => $validated['company_description'] ?? '',
        ];

        if ($request->hasFile('company_logo')) {
            $logoPayload = MediaHelper::upload($request->file('company_logo'), 'settings/branding');
            $data['company_logo'] = $logoPayload;
        }

        if ($request->hasFile('company_favicon')) {
            $faviconPayload = MediaHelper::upload($request->file('company_favicon'), 'settings/branding');
            $data['company_favicon'] = $faviconPayload;
        }

        $this->settingService->setMany($data, 'company');

        return redirect()
            ->route('settings', ['tab' => 'company'])
            ->with('success', _trans('common.Company branding and details updated successfully.'));
    }

    /**
     * Update localization settings.
     */
    public function updateLocalization(UpdateLocalizationSettingRequest $request): RedirectResponse
    {
        $this->settingService->setMany($request->validated(), 'localization');

        return redirect()
            ->route('settings', ['tab' => 'localization'])
            ->with('success', _trans('common.Localization settings updated successfully.'));
    }

    /**
     * Update attendance rules and timing settings.
     */
    public function updateAttendance(UpdateAttendanceSettingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['overtime_enabled'] = $request->boolean('overtime_enabled') ? '1' : '0';

        $this->settingService->setMany($data, 'attendance');

        return redirect()
            ->route('settings', ['tab' => 'attendance'])
            ->with('success', _trans('common.Attendance settings updated successfully.'));
    }

    /**
     * Update leave quotas and approval settings.
     */
    public function updateLeave(UpdateLeaveSettingRequest $request): RedirectResponse
    {
        $this->settingService->setMany($request->validated(), 'leave');

        return redirect()
            ->route('settings', ['tab' => 'leave'])
            ->with('success', _trans('common.Leave settings updated successfully.'));
    }

    /**
     * Update payroll calculation settings.
     */
    public function updatePayroll(UpdatePayrollSettingRequest $request): RedirectResponse
    {
        $this->settingService->setMany($request->validated(), 'payroll');

        return redirect()
            ->route('settings', ['tab' => 'payroll'])
            ->with('success', _trans('common.Payroll settings updated successfully.'));
    }

    /**
     * Update mail server configuration.
     */
    public function updateMail(UpdateMailSettingRequest $request): RedirectResponse
    {
        $this->settingService->setMany($request->validated(), 'mail');

        return redirect()
            ->route('settings', ['tab' => 'mail'])
            ->with('success', _trans('common.Mail configuration updated successfully.'));
    }
}
