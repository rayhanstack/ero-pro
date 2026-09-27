<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payslip - {{ $payslip->payslip_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
            margin: 0;
        }
        .company-details {
            font-size: 10px;
            color: #64748b;
            margin-top: 4px;
        }
        .payslip-title {
            text-align: right;
        }
        .badge {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 18px;
        }
        .meta-table td {
            padding: 4px 6px;
            font-size: 11px;
            vertical-align: top;
        }
        .meta-label {
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
        }
        .meta-val {
            font-weight: bold;
            color: #0f172a;
        }
        .attendance-bar {
            margin-bottom: 18px;
        }
        .attendance-table {
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .attendance-table th {
            background-color: #f1f5f9;
            padding: 6px;
            font-size: 10px;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .attendance-table td {
            padding: 6px;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #e2e8f0;
        }
        .breakdown-table-wrapper {
            margin-bottom: 20px;
        }
        .breakdown-table {
            width: 100%;
            border: 1px solid #cbd5e1;
        }
        .breakdown-table th {
            padding: 8px;
            font-size: 11px;
            border-bottom: 1px solid #cbd5e1;
        }
        .breakdown-table td {
            padding: 6px 8px;
            font-size: 11px;
            border-bottom: 1px solid #f1f5f9;
        }
        .th-earning {
            background-color: #ecfdf5;
            color: #065f46;
            text-align: left;
        }
        .th-deduction {
            background-color: #fef2f2;
            color: #991b1b;
            text-align: left;
        }
        .tfoot-row {
            background-color: #f8fafc;
            font-weight: bold;
            border-top: 2px solid #cbd5e1;
        }
        .net-pay-banner {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 25px;
        }
        .net-pay-amount {
            font-size: 20px;
            font-weight: bold;
            color: #059669;
        }
        .signatures {
            margin-top: 50px;
            width: 100%;
        }
        .signature-line {
            border-top: 1px solid #94a3b8;
            width: 180px;
            margin: 0 auto;
            padding-top: 5px;
            font-size: 10px;
            color: #64748b;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <table class="header-table">
            <tr>
                <td style="width: 60%;">
                    <div class="company-name">{{ $company['name'] }}</div>
                    <div class="company-details">
                        {{ $company['address'] }}<br>
                        Email: {{ $company['email'] }} | Phone: {{ $company['phone'] }}
                    </div>
                </td>
                <td class="payslip-title" style="width: 40%;">
                    <span class="badge">OFFICIAL PAYSLIP</span>
                    <h3 style="margin: 6px 0 0 0; color: #0f172a;">{{ $payslip->payrollPeriod?->formatted_period }}</h3>
                    <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                        Slip #: <strong>{{ $payslip->payslip_number }}</strong><br>
                        Date: {{ $payslip->created_at->format('M d, Y') }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- Employee Details --}}
        <div class="meta-box">
            <table class="meta-table">
                <tr>
                    <td style="width: 25%;">
                        <div class="meta-label">Employee Name</div>
                        <div class="meta-val">{{ $payslip->employee?->name }}</div>
                    </td>
                    <td style="width: 25%;">
                        <div class="meta-label">Employee Code</div>
                        <div class="meta-val">{{ $payslip->employee?->employeeDetail?->emp_code ?? 'EMP' }}</div>
                    </td>
                    <td style="width: 25%;">
                        <div class="meta-label">Department</div>
                        <div class="meta-val">{{ $payslip->employee?->employeeDetail?->department?->name ?? 'General' }}</div>
                    </td>
                    <td style="width: 25%;">
                        <div class="meta-label">Designation</div>
                        <div class="meta-val">{{ $payslip->employee?->employeeDetail?->designation?->name ?? 'Staff' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="meta-label">Payment Method</div>
                        <div class="meta-val">{{ $payslip->payment_method ?? 'Bank Transfer' }}</div>
                    </td>
                    <td>
                        <div class="meta-label">Bank Name</div>
                        <div class="meta-val">{{ $payslip->employee?->primaryBankAccount?->bank ?? 'Bank' }}</div>
                    </td>
                    <td>
                        <div class="meta-label">Account No</div>
                        <div class="meta-val">{{ $payslip->employee?->primaryBankAccount?->account_no ?? '••••' }}</div>
                    </td>
                    <td>
                        <div class="meta-label">Disbursed Date</div>
                        <div class="meta-val">{{ $payslip->paid_at ? $payslip->paid_at->format('M d, Y') : 'Pending' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Attendance Bar --}}
        <div class="attendance-bar">
            <table class="attendance-table">
                <tr>
                    <th>Working Days</th>
                    <th>Present Days</th>
                    <th>Leave Days</th>
                    <th>Absent Days</th>
                </tr>
                <tr>
                    <td>{{ number_format($payslip->working_days, 1) }}</td>
                    <td style="color: #059669;">{{ number_format($payslip->present_days, 1) }}</td>
                    <td style="color: #0284c7;">{{ number_format($payslip->leave_days, 1) }}</td>
                    <td style="color: #dc2626;">{{ number_format($payslip->absent_days, 1) }}</td>
                </tr>
            </table>
        </div>

        {{-- Earnings and Deductions 2-Column Table --}}
        <div class="breakdown-table-wrapper">
            <table>
                <tr>
                    {{-- Earnings Column --}}
                    <td style="width: 50%; vertical-align: top; padding-right: 8px;">
                        <table class="breakdown-table">
                            <thead>
                                <tr>
                                    <th class="th-earning">Earnings / Allowances</th>
                                    <th class="th-earning text-right">Amount ($)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Basic Salary</strong></td>
                                    <td class="text-right"><strong>{{ number_format($payslip->basic, 2) }}</strong></td>
                                </tr>
                                @foreach ($payslip->earnings as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                                @if ($payslip->overtime_amount > 0)
                                    <tr>
                                        <td>Overtime Pay</td>
                                        <td class="text-right">{{ number_format($payslip->overtime_amount, 2) }}</td>
                                    </tr>
                                @endif
                                @if ($payslip->bonus > 0)
                                    <tr>
                                        <td>Bonus / Incentive</td>
                                        <td class="text-right">{{ number_format($payslip->bonus, 2) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr class="tfoot-row">
                                    <td>Total Gross Pay</td>
                                    <td class="text-right" style="color: #059669;">${{ number_format($payslip->gross_salary, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>

                    {{-- Deductions Column --}}
                    <td style="width: 50%; vertical-align: top; padding-left: 8px;">
                        <table class="breakdown-table">
                            <thead>
                                <tr>
                                    <th class="th-deduction">Deductions & Taxes</th>
                                    <th class="th-deduction text-right">Amount ($)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payslip->deductions as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                                @if ($payslip->absent_deduction > 0)
                                    <tr>
                                        <td>Absenteeism Deduction</td>
                                        <td class="text-right">{{ number_format($payslip->absent_deduction, 2) }}</td>
                                    </tr>
                                @endif
                                @if ($payslip->tax > 0)
                                    <tr>
                                        <td>Income Tax</td>
                                        <td class="text-right">{{ number_format($payslip->tax, 2) }}</td>
                                    </tr>
                                @endif
                                @if ($payslip->deductions->isEmpty() && $payslip->absent_deduction <= 0 && $payslip->tax <= 0)
                                    <tr>
                                        <td colspan="2" class="text-center" style="color: #94a3b8; font-style: italic; padding: 20px 0;">No deductions</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr class="tfoot-row">
                                    <td>Total Deductions</td>
                                    <td class="text-right" style="color: #dc2626;">-${{ number_format($payslip->total_deductions + $payslip->absent_deduction + $payslip->tax, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Net Pay Banner --}}
        <div class="net-pay-banner">
            <table>
                <tr>
                    <td>
                        <div style="font-size: 10px; color: #059669; text-transform: uppercase; font-weight: bold;">Net Salary Payable</div>
                        <div class="net-pay-amount">${{ number_format($payslip->net_pay, 2) }}</div>
                    </td>
                    <td class="text-right" style="font-size: 11px; color: #475569;">
                        Gross Earnings: <strong>${{ number_format($payslip->gross_salary, 2) }}</strong><br>
                        Total Deductions: <strong style="color: #dc2626;">-${{ number_format($payslip->total_deductions + $payslip->absent_deduction + $payslip->tax, 2) }}</strong>
                    </td>
                </tr>
            </table>
        </div>

        @if ($payslip->note)
            <div style="font-size: 10px; color: #475569; background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 8px; border-radius: 4px; margin-bottom: 20px;">
                <strong>Remarks:</strong> {{ $payslip->note }}
            </div>
        @endif

        {{-- Signatures --}}
        <table class="signatures">
            <tr>
                <td style="width: 50%; text-align: center;">
                    <div class="signature-line">Employee Signature</div>
                </td>
                <td style="width: 50%; text-align: center;">
                    <div class="signature-line">Authorized Signatory</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
