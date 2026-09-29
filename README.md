# ERP Pro — Enterprise Resource Planning & Business Management System

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap 5">
  <img src="https://img.shields.io/badge/Chart.js-4.x-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white" alt="Chart.js">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License">
</p>

---

## 🎯 Main Purpose & Overview for Developers

**ERP Pro** is a modern, modular, and extensible enterprise resource planning application built with **Laravel 12**, **PHP 8.2+**, and **Bootstrap 5**. It provides businesses, agencies, and enterprises with an integrated hub to manage:

1. **Human Resources & Workforce** (Employees, Departments, Designations, Shifts, Holidays, Weekends)
2. **Time & Attendance Tracking** (Daily punch in/out, automatic late/overtime calculation, monthly matrix, regularization requests)
3. **Leave Management** (Leave types, employee balances, approval workflows, calendar overview)
4. **Client & CRM Operations** (Client profiles, multiple contact persons, notes, financials)
5. **Projects & Kanban Task Pipelines** (Milestones, drag-and-drop Kanban status movements, subtasks, checklists, file attachments, task comments)
6. **Meetings & Company Calendar** (RSVP responses, scheduling conflict detection, minutes of meeting recording)
7. **Payroll Engine & Payslips** (Configurable salary components, calculation rules, payroll periods, auto-generated payslips, PDF generation)
8. **Finance, Accounts & Invoicing** (Multi-account live balance ledger, inter-account transfers, income/expense tracking, line-item client invoices, DomPDF exports, automated payroll expense synchronization)
9. **Executive & Personalized Dashboards** (Cached 5-min real-time business KPIs, Chart.js cash-flow and project status visualizations, role-tailored employee portals)
10. **Role-Based Access Control (RBAC)** (Fine-grained Spatie permission gates, customizable role matrix)
11. **Localization & Multilingual Engine** (Full English and Bengali translations, RTL readiness, audit trail logging)

---

## 🏗️ Architecture & Core Design Patterns

The codebase adheres strictly to clean architectural principles to ensure maintainability and testability:

```
app/
├── Enums/                     # PHP 8.2 Backed Enums for statuses, types, and options
├── Events/                    # Domain events (e.g. PayslipPaid)
├── Helpers/                   # TranslationManager, MediaHelper, currency_format, global settings
├── Http/
│   ├── Controllers/Admin/     # Thin controllers handling HTTP requests and responses
│   └── Requests/              # Dedicated FormRequests handling validation rules and authorization
├── Listeners/                 # Decoupled event listeners (e.g. CreateExpenseFromPayslip)
├── Models/                    # Eloquent models with typed casts, relations, query scopes, and ActivityLog traits
├── Providers/                 # Service providers
└── Services/                  # Business logic services (DashboardService, InvoiceService, PayrollService, etc.)
```

### Key Architectural Guidelines
- **Thin Controllers, Rich Services**: Controllers only orchestrate requests and delegates business logic to dedicated service classes (`app/Services/{Module}`).
- **Strict Form Validation**: All user inputs are validated through discrete FormRequests (`app/Http/Requests/{Module}`).
- **Data Integrity & Transactions**: All multi-table database operations are wrapped in `DB::transaction(...)`.
- **Soft Deletes**: Main entities use `SoftDeletes` to prevent irreversible data loss.
- **Eager Loading**: Relations are eager-loaded to prevent N+1 query bottlenecks (`with([...])`).
- **Currency & Precision**: Monetary values are stored as `decimal(15,2)` and rendered via `currency_format($amount)`.
- **Localization**: User-facing strings use `_trans('common.Key')` allowing seamless switching between English and Bengali (`lang/en`, `lang/bn`).
- **Authorization**: Protected via Spatie Laravel Permission using `module.action` convention (e.g. `finance.view`, `payroll.process`).

---

## 🚀 Quick Start & Installation Guide

### Prerequisites
- **PHP**: `>= 8.2` (with `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `gd` or `imagick`, `bcmath`)
- **Composer**: `>= 2.0`
- **Node.js & NPM**: `>= 18.x`
- **Database**: MySQL `>= 8.0` or MariaDB `>= 10.4`

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/rayhanstack/ero-pro.git
cd ero-pro

# Install PHP dependencies
composer install

# Install Frontend assets & build
npm install
npm run build
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env` database settings:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_pro
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Database Migration & Seeding
Run the database migrations along with the rich seeders to populate initial roles, admin users, dummy employees, payroll structures, accounts, and demo transactions:

```bash
php artisan migrate:fresh --seed
```

### 4. Storage Linking & Dev Server
```bash
php artisan storage:link
php artisan serve
```

Access the application in your browser at: `http://127.0.0.1:8000`

---

## 🔑 Default Credentials

| Role | Email | Password | Access Level |
|---|---|---|---|
| **Super Admin** | `admin@erp.test` (or `admin@erppro.com`) | `12345678` | Full administrative access to all modules |
| **HR Manager** | `hr@erp.test` | `12345678` | Workforce, attendance, leaves, and recruitment |
| **Project Manager** | `manager@erp.test` | `12345678` | Projects, tasks, teams, client deliverables |
| **Employee** | `staff@erp.test` | `12345678` | Self-service portal: punch in/out, tasks, leaves, payslips |

---

## 📦 Detailed Module Capabilities

### 1. Dashboard & Business Intelligence
- **Real-Time KPIs**: Total Revenue, Active Projects, Pending Tasks, Total Clients with growth indicators.
- **Workforce Health Bar**: Real-time counters of staff Present, On Leave, and Late entries for the day.
- **Chart.js Visualizations**: 6-month historical Cash Flow (Revenue vs Expense) and Project Status Distribution.
- **5-Minute Cache Layer**: Aggregated stats cached via `App\Services\DashboardService` to minimize DB queries.

### 2. HR & Employee Management
- **Department & Designation Structure**: Hierarchical department trees and designations.
- **Shift & Work Hours**: Configurable shifts with start/end times and late tolerance thresholds.
- **Holidays & Weekends**: Flexible weekend configuration and holiday calendar management.
- **Employee Profiles**: Personal information, salary setups, emergency contacts, identity documents, and bank details.

### 3. Attendance & Leave Workflows
- **Punch In/Out**: Browser and IP-aware attendance punching with automatic working hour calculations.
- **Attendance Matrix**: Visual monthly grid view with color-coded status badges (`Present`, `Late`, `Absent`, `Leave`, `Half-Day`).
- **Regularizations**: Employee attendance adjustment submission and HR approval pipeline.
- **Leave Quotas**: Annual leave allowances with dynamic balance deductions upon approval.

### 4. Projects & Kanban Task Board
- **Projects**: Budgeting, deadlines, client assignment, team leads, and calculated completion progress.
- **Kanban Pipeline**: Interactive drag-and-drop task status board (`To Do`, `In Progress`, `Review`, `Done`).
- **Task Collaboration**: Checklist items, task comments, file attachments, priority flags, and assignee management.

### 5. Payroll & Compensation
- **Salary Components**: Earnings and Deductions with Fixed or Percentage-of-Basic formula calculation types.
- **Payroll Cycles**: Monthly period creation, salary calculations, approval checkpoints, and mass payment execution.
- **Payslip Generator**: Itemized monthly payslips with one-click clean DomPDF export.

### 6. Finance, Invoices & Banking
- **Multi-Account Live Ledger**: Accounts for `Bank`, `Cash`, and `Mobile Banking` (bKash/Nagad/Rocket) with dynamically calculated live balances.
- **Inter-Account Transfers**: Fund movements with automated debit/credit balancing.
- **Income & Expense Tracking**: Categorized transactions with project, client, or employee tags.
- **Client Invoices**: Itemized invoices with custom tax %, discounts, payment tracking, and PDF download.
- **Automated Hooks**: `PayslipPaid` automatically posts an expense to the finance ledger.

---

## 🧪 Testing & Quality Assurance

The project includes an extensive **Pest & Feature Test Suite** covering all modules:

```bash
# Run all tests
php artisan test

# Run a specific module test suite
php artisan test tests/Feature/FinanceManagementTest.php
php artisan test tests/Feature/DashboardTest.php
php artisan test tests/Feature/AttendanceManagementTest.php
php artisan test tests/Feature/PayrollCalculationTest.php
```

---

## 🛠️ Developer Checklist for Adding New Modules

When building or extending features in ERP Pro, adhere to the standard module lifecycle:

1. **Migration**: Create schema with proper foreign keys, decimals, indexes, and soft deletes (`database/migrations`).
2. **Model**: Define `$fillable`, typed `$casts`, Eloquent relationships, query scopes, and add `LogsActivity` trait (`app/Models`).
3. **Enums**: Create backed enums with translatable labels and badge classes (`app/Enums`).
4. **FormRequest**: Validate authorization and input rules (`app/Http/Requests/{Module}`).
5. **Service**: Encapsulate DB operations within `DB::transaction(...)` (`app/Services/{Module}`).
6. **Controller**: Keep controllers thin, delegating work to the Service (`app/Http/Controllers/Admin/{Module}`).
7. **Routes**: Register named routes inside `routes/admin.php` protected by `can:{module}.{action}` middleware.
8. **Views**: Build responsive Blade templates using components (`x-form.*`, `x-ui.*`, `admin.layouts.app`).
9. **Translations**: Ensure all strings use `_trans('common.Key')` and exist in `lang/en/common.json` and `lang/bn/common.json`.
10. **Sidebar**: Register navigation entry in `resources/views/admin/layouts/inc/sidebar.blade.php`.
11. **Feature Tests**: Write comprehensive test coverage in `tests/Feature/`.

---

## 📄 License

ERP Pro is open-sourced software licensed under the [MIT license](LICENSE).
