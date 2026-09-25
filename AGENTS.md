Project: ERP Pro, Laravel 12, PHP 8.2, MySQL, Bootstrap 5 admin UI (already built), jQuery, Select2, Chart.js. No Tailwind in admin UI.

Rules:

- Keep the existing look. Reuse layout admin.layouts.app, sidebar/navbar, and existing components x-form._, x-ui._. Create new reusable Blade components instead of duplicating markup.
- Structure: app/Http/Controllers/Admin/{Module}, app/Http/Requests/{Module}, app/Services/{Module}, app/Enums, app/Models. Controllers thin, logic in Services, validation in Form Requests.
- Every module follows: Migration -> Model (fillable, casts, relations, scopes) -> Enum -> FormRequest -> Service -> Controller -> routes (in routes/admin.php, named admin-style e.g. employees.index) -> Blade (index, create, edit, show) -> permission -> sidebar item -> Pest feature test.
- Use SoftDeletes on main entities. Wrap multi-table writes in DB::transaction. Eager load to avoid N+1. Add DB indexes and foreign keys.
- All user-facing strings via \_trans('common.Key'). Never hardcode secrets. Do not commit .env.
- Use backed Enums for status/type. Money is decimal(15,2). Dates use settings-based format helpers.
- Authorization: spatie/laravel-permission, permission names "module.action" (employee.view, employee.create, employee.edit, employee.delete).
- Flash messages via session success/error shown as toast in layout.
- Do not change unrelated files. After work, list files created/changed and how to test. Run php artisan migrate:fresh --seed and php artisan test to confirm nothing breaks.
- Never fabricate packages. Ask before adding a Composer package not listed in the prompt.
