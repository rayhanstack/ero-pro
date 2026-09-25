<?php

namespace Database\Seeders;

use App\Enums\BloodGroupEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\MaritalStatusEnum;
use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmergencyContact;
use App\Models\Shift;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $country = Country::where('iso2', 'BD')->first() ?? Country::first();
        $state = State::where('name', 'Dhaka')->first() ?? State::first();
        $city = City::where('name', 'Dhaka')->first() ?? City::first();

        $shifts = Shift::all();
        $dayShift = $shifts->where('name', 'Day Shift')->first() ?? $shifts->first();

        $departments = Department::all()->keyBy('code');
        $eng = $departments->get('ENG');
        $hr = $departments->get('HR');
        $fin = $departments->get('FIN');
        $sal = $departments->get('SAL');
        $ops = $departments->get('OPS');

        $designations = Designation::all();

        $demoEmployeesData = [
            // Engineering Managers / Leads
            [
                'first_name' => 'Tariqul',
                'last_name' => 'Islam',
                'email' => 'tariqul.lead@erp.test',
                'phone' => '+8801711000001',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'ENG',
                'desig_name' => 'Tech Lead',
                'salary' => 140000,
                'joining_date' => '2022-01-15',
                'is_dept_head' => true,
            ],
            // HR Manager
            [
                'first_name' => 'Nusrat',
                'last_name' => 'Jahan',
                'email' => 'nusrat.hr@erp.test',
                'phone' => '+8801711000002',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'HR',
                'desig_name' => 'HR Manager',
                'salary' => 110000,
                'joining_date' => '2022-03-01',
                'is_dept_head' => true,
            ],
            // Finance Manager
            [
                'first_name' => 'Mahmudul',
                'last_name' => 'Hasan',
                'email' => 'mahmud.fin@erp.test',
                'phone' => '+8801711000003',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'FIN',
                'desig_name' => 'Finance Manager',
                'salary' => 125000,
                'joining_date' => '2021-11-01',
                'is_dept_head' => true,
            ],
            // Sales Account Manager
            [
                'first_name' => 'Farhana',
                'last_name' => 'Akter',
                'email' => 'farhana.sales@erp.test',
                'phone' => '+8801711000004',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'SAL',
                'desig_name' => 'Account Manager',
                'salary' => 95000,
                'joining_date' => '2022-05-10',
                'is_dept_head' => true,
            ],
            // Operations Lead
            [
                'first_name' => 'Kamrul',
                'last_name' => 'Hossain',
                'email' => 'kamrul.ops@erp.test',
                'phone' => '+8801711000005',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'OPS',
                'desig_name' => 'Office Administrator',
                'salary' => 65000,
                'joining_date' => '2022-06-01',
                'is_dept_head' => true,
            ],
            // Engineering Team
            [
                'first_name' => 'Rafiqul',
                'last_name' => 'Karim',
                'email' => 'rafiqul.dev@erp.test',
                'phone' => '+8801711000006',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'ENG',
                'desig_name' => 'Senior Software Engineer',
                'salary' => 105000,
                'joining_date' => '2023-01-10',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            [
                'first_name' => 'Sadia',
                'last_name' => 'Rahman',
                'email' => 'sadia.dev@erp.test',
                'phone' => '+8801711000007',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'ENG',
                'desig_name' => 'Senior Software Engineer',
                'salary' => 100000,
                'joining_date' => '2023-02-15',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            [
                'first_name' => 'Tanvir',
                'last_name' => 'Ahmed',
                'email' => 'tanvir.dev@erp.test',
                'phone' => '+8801711000008',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'ENG',
                'desig_name' => 'Software Engineer',
                'salary' => 75000,
                'joining_date' => '2023-06-01',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            [
                'first_name' => 'Mehedi',
                'last_name' => 'Hasan',
                'email' => 'mehedi.dev@erp.test',
                'phone' => '+8801711000009',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'ENG',
                'desig_name' => 'Software Engineer',
                'salary' => 70000,
                'joining_date' => '2023-07-15',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            [
                'first_name' => 'Ayesha',
                'last_name' => 'Siddiqa',
                'email' => 'ayesha.qa@erp.test',
                'phone' => '+8801711000010',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'ENG',
                'desig_name' => 'QA Engineer',
                'salary' => 65000,
                'joining_date' => '2023-08-01',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            [
                'first_name' => 'Shakil',
                'last_name' => 'Khan',
                'email' => 'shakil.jrdev@erp.test',
                'phone' => '+8801711000011',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'ENG',
                'desig_name' => 'Junior Software Engineer',
                'salary' => 45000,
                'joining_date' => '2024-01-01',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            [
                'first_name' => 'Fatima',
                'last_name' => 'Zahra',
                'email' => 'fatima.jrdev@erp.test',
                'phone' => '+8801711000012',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'ENG',
                'desig_name' => 'Junior Software Engineer',
                'salary' => 45000,
                'joining_date' => '2024-02-15',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            // HR Team
            [
                'first_name' => 'Anisur',
                'last_name' => 'Rahman',
                'email' => 'anis.hr@erp.test',
                'phone' => '+8801711000013',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'HR',
                'desig_name' => 'HR Executive',
                'salary' => 50000,
                'joining_date' => '2023-03-10',
                'manager_email' => 'nusrat.hr@erp.test',
            ],
            [
                'first_name' => 'Sumaiya',
                'last_name' => 'Khatun',
                'email' => 'sumaiya.hr@erp.test',
                'phone' => '+8801711000014',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'HR',
                'desig_name' => 'HR Executive',
                'salary' => 48000,
                'joining_date' => '2023-09-01',
                'manager_email' => 'nusrat.hr@erp.test',
            ],
            // Finance Team
            [
                'first_name' => 'Shahidul',
                'last_name' => 'Alam',
                'email' => 'shahid.acc@erp.test',
                'phone' => '+8801711000015',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'FIN',
                'desig_name' => 'Accountant',
                'salary' => 55000,
                'joining_date' => '2023-04-15',
                'manager_email' => 'mahmud.fin@erp.test',
            ],
            [
                'first_name' => 'Rubina',
                'last_name' => 'Parvin',
                'email' => 'rubina.acc@erp.test',
                'phone' => '+8801711000016',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'FIN',
                'desig_name' => 'Accountant',
                'salary' => 52000,
                'joining_date' => '2023-10-01',
                'manager_email' => 'mahmud.fin@erp.test',
            ],
            // Sales Team
            [
                'first_name' => 'Imran',
                'last_name' => 'Chowdhury',
                'email' => 'imran.sales@erp.test',
                'phone' => '+8801711000017',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'SAL',
                'desig_name' => 'Sales Executive',
                'salary' => 45000,
                'joining_date' => '2023-05-01',
                'manager_email' => 'farhana.sales@erp.test',
            ],
            [
                'first_name' => 'Mithila',
                'last_name' => 'Ferdous',
                'email' => 'mithila.sales@erp.test',
                'phone' => '+8801711000018',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'SAL',
                'desig_name' => 'Sales Executive',
                'salary' => 46000,
                'joining_date' => '2023-11-15',
                'manager_email' => 'farhana.sales@erp.test',
            ],
            [
                'first_name' => 'Nazmul',
                'last_name' => 'Huda',
                'email' => 'nazmul.sales@erp.test',
                'phone' => '+8801711000019',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'SAL',
                'desig_name' => 'Sales Executive',
                'salary' => 44000,
                'joining_date' => '2024-01-10',
                'manager_email' => 'farhana.sales@erp.test',
            ],
            // Additional Engineering & Ops
            [
                'first_name' => 'Jannatul',
                'last_name' => 'Naim',
                'email' => 'jannat.dev@erp.test',
                'phone' => '+8801711000020',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'ENG',
                'desig_name' => 'Software Engineer',
                'salary' => 72000,
                'joining_date' => '2023-12-01',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            [
                'first_name' => 'Arifur',
                'last_name' => 'Rahman',
                'email' => 'arif.ops@erp.test',
                'phone' => '+8801711000021',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'OPS',
                'desig_name' => 'Office Administrator',
                'salary' => 40000,
                'joining_date' => '2024-02-01',
                'manager_email' => 'kamrul.ops@erp.test',
            ],
            [
                'first_name' => 'Tahmina',
                'last_name' => 'Akter',
                'email' => 'tahmina.dev@erp.test',
                'phone' => '+8801711000022',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'ENG',
                'desig_name' => 'Junior Software Engineer',
                'salary' => 42000,
                'joining_date' => '2024-03-01',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            [
                'first_name' => 'Mustafizur',
                'last_name' => 'Rahman',
                'email' => 'mustafiz.dev@erp.test',
                'phone' => '+8801711000023',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'ENG',
                'desig_name' => 'QA Engineer',
                'salary' => 60000,
                'joining_date' => '2023-07-01',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            [
                'first_name' => 'Nasrin',
                'last_name' => 'Sultana',
                'email' => 'nasrin.dev@erp.test',
                'phone' => '+8801711000024',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'ENG',
                'desig_name' => 'Software Engineer',
                'salary' => 68000,
                'joining_date' => '2023-08-15',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            [
                'first_name' => 'Golam',
                'last_name' => 'Rabbani',
                'email' => 'rabbani.fin@erp.test',
                'phone' => '+8801711000025',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'FIN',
                'desig_name' => 'Accountant',
                'salary' => 50000,
                'joining_date' => '2024-01-15',
                'manager_email' => 'mahmud.fin@erp.test',
            ],
            [
                'first_name' => 'Sultana',
                'last_name' => 'Razia',
                'email' => 'razia.sales@erp.test',
                'phone' => '+8801711000026',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'SAL',
                'desig_name' => 'Sales Executive',
                'salary' => 45000,
                'joining_date' => '2024-02-10',
                'manager_email' => 'farhana.sales@erp.test',
            ],
            [
                'first_name' => 'Kawsar',
                'last_name' => 'Ahmed',
                'email' => 'kawsar.dev@erp.test',
                'phone' => '+8801711000027',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'ENG',
                'desig_name' => 'Software Engineer',
                'salary' => 74000,
                'joining_date' => '2023-09-01',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            [
                'first_name' => 'Nabila',
                'last_name' => 'Hossain',
                'email' => 'nabila.hr@erp.test',
                'phone' => '+8801711000028',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'HR',
                'desig_name' => 'HR Executive',
                'salary' => 45000,
                'joining_date' => '2024-03-15',
                'manager_email' => 'nusrat.hr@erp.test',
            ],
            [
                'first_name' => 'Zubair',
                'last_name' => 'Iqbal',
                'email' => 'zubair.dev@erp.test',
                'phone' => '+8801711000029',
                'gender' => GenderEnum::MALE,
                'dept_code' => 'ENG',
                'desig_name' => 'Junior Software Engineer',
                'salary' => 40000,
                'joining_date' => '2024-04-01',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
            [
                'first_name' => 'Lamia',
                'last_name' => 'Tabassum',
                'email' => 'lamia.qa@erp.test',
                'phone' => '+8801711000030',
                'gender' => GenderEnum::FEMALE,
                'dept_code' => 'ENG',
                'desig_name' => 'QA Engineer',
                'salary' => 58000,
                'joining_date' => '2023-10-15',
                'manager_email' => 'tariqul.lead@erp.test',
            ],
        ];

        $bloodGroups = BloodGroupEnum::cases();
        $maritalStatuses = MaritalStatusEnum::cases();
        $banks = ['Dutch-Bangla Bank', 'BRAC Bank', 'City Bank', 'Eastern Bank PLC', 'Mutual Trust Bank'];

        $createdEmployees = [];

        foreach ($demoEmployeesData as $index => $data) {
            $dept = $departments->get($data['dept_code']);
            $desig = $designations->where('department_id', $dept?->id)->where('name', $data['desig_name'])->first();
            $shift = $shifts->count() > 0 ? $shifts[$index % $shifts->count()] : $dayShift;

            $joiningDate = Carbon::parse($data['joining_date']);
            $confirmationDate = (clone $joiningDate)->addMonths(3);

            $empCode = 'EMP-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            $employee = Employee::updateOrCreate(
                ['email' => $data['email']],
                [
                    'emp_code' => $empCode,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => $data['phone'],
                    'dob' => Carbon::now()->subYears(24 + ($index % 15))->subDays($index * 12)->format('Y-m-d'),
                    'gender' => $data['gender'],
                    'marital_status' => $maritalStatuses[$index % count($maritalStatuses)],
                    'nid' => '199' . str_pad((string) ($index + 10000000), 10, '0', STR_PAD_LEFT),
                    'blood_group' => $bloodGroups[$index % count($bloodGroups)],
                    'department_id' => $dept?->id,
                    'designation_id' => $desig?->id,
                    'shift_id' => $shift?->id,
                    'joining_date' => $joiningDate->format('Y-m-d'),
                    'confirmation_date' => $confirmationDate->format('Y-m-d'),
                    'employment_type' => EmploymentTypeEnum::FULL_TIME,
                    'status' => EmployeeStatusEnum::ACTIVE,
                    'basic_salary' => $data['salary'],
                    'country_id' => $country?->id,
                    'state_id' => $state?->id,
                    'city_id' => $city?->id,
                    'present_address' => 'House ' . ($index + 12) . ', Road ' . (($index % 10) + 1) . ', Dhanmondi, Dhaka',
                    'permanent_address' => 'Village ' . ($index + 1) . ', Post Office ' . ($index + 5) . ', Dhaka',
                ]
            );

            $createdEmployees[$data['email']] = $employee;

            // If marked as department head, update department.head_id
            if (!empty($data['is_dept_head']) && $dept) {
                $dept->update(['head_id' => $employee->id]);
            }

            // Create Primary Bank Account
            EmployeeBankAccount::firstOrCreate(
                ['employee_id' => $employee->id, 'account_no' => '10215' . str_pad((string) ($index + 100000), 8, '0', STR_PAD_LEFT)],
                [
                    'bank' => $banks[$index % count($banks)],
                    'branch' => 'Gulshan Branch',
                    'account_name' => $employee->full_name,
                    'routing_number' => '09027' . str_pad((string) ($index + 100), 4, '0', STR_PAD_LEFT),
                    'swift_code' => 'DBBLBDDH',
                    'is_primary' => true,
                ]
            );

            // Create Emergency Contact
            EmployeeEmergencyContact::firstOrCreate(
                ['employee_id' => $employee->id, 'phone' => '+8801811' . str_pad((string) ($index + 100000), 6, '0', STR_PAD_LEFT)],
                [
                    'name' => 'Emergency Contact ' . ($index + 1),
                    'relationship' => $index % 2 === 0 ? 'Spouse' : 'Parent',
                    'alt_phone' => '+8801911' . str_pad((string) ($index + 100000), 6, '0', STR_PAD_LEFT),
                    'address' => 'Dhanmondi, Dhaka',
                ]
            );

            // Create Document
            EmployeeDocument::firstOrCreate(
                ['employee_id' => $employee->id, 'title' => 'National ID Card'],
                [
                    'file' => 'documents/nid_' . $employee->emp_code . '.pdf',
                    'expiry_date' => Carbon::now()->addYears(5)->format('Y-m-d'),
                ]
            );
        }

        // Link Managers
        foreach ($demoEmployeesData as $data) {
            if (!empty($data['manager_email']) && isset($createdEmployees[$data['manager_email']])) {
                $employee = $createdEmployees[$data['email']];
                $manager = $createdEmployees[$data['manager_email']];
                $employee->update(['manager_id' => $manager->id]);
            }
        }
    }
}
