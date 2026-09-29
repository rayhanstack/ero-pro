<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333333;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-title {
            font-size: 22px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 4px;
        }
        .company-info {
            font-size: 11px;
            color: #64748b;
            line-height: 1.4;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .invoice-meta {
            text-align: right;
            font-size: 11px;
            color: #475569;
            line-height: 1.5;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 12px;
        }
        .meta-table td {
            vertical-align: top;
            padding: 8px 12px;
        }
        .section-label {
            font-size: 9px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .client-name {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .client-info {
            font-size: 11px;
            color: #475569;
            line-height: 1.4;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 10px;
            border-bottom: 1px solid #cbd5e1;
            text-align: left;
        }
        .items-table td {
            padding: 10px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .totals-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .totals-table td {
            padding: 6px 8px;
            font-size: 11px;
        }
        .totals-table .total-row {
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .notes-box {
            background-color: #f8fafc;
            border-left: 3px solid #2563eb;
            padding: 10px 14px;
            font-size: 10px;
            color: #475569;
            margin-top: 20px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-paid {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-partial {
            background-color: #fef9c3;
            color: #854d0e;
        }
        .badge-sent {
            background-color: #e0f2fe;
            color: #075985;
        }
        .badge-draft {
            background-color: #f1f5f9;
            color: #475569;
        }
        .badge-overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td>
                <div class="company-title">ERP Pro</div>
                <div class="company-info">
                    Enterprise Resource Planning & Business Solutions<br>
                    Dhaka, Bangladesh<br>
                    Email: billing@erp.test | Phone: +880 1700-000000
                </div>
            </td>
            <td>
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-meta">
                    <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                    <strong>Issue Date:</strong> {{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : '—' }}<br>
                    <strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}<br>
                    <strong>Status:</strong> 
                    <span class="badge badge-{{ $invoice->status->value }}">{{ $invoice->status->label() }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- Meta --}}
    <table class="meta-table">
        <tr>
            <td style="width: 55%;">
                <div class="section-label">Billed To</div>
                @if($invoice->client)
                    <div class="client-name">{{ $invoice->client->name }}</div>
                    <div class="client-info">
                        @if($invoice->client->company_name)
                            {{ $invoice->client->company_name }}<br>
                        @endif
                        {{ $invoice->client->email }}
                        @if($invoice->client->phone)
                            | {{ $invoice->client->phone }}
                        @endif
                        @if($invoice->client->address)
                            <br>{{ $invoice->client->address }}
                        @endif
                    </div>
                @else
                    <div class="client-info">—</div>
                @endif
            </td>
            <td style="width: 45%;">
                @if($invoice->project)
                    <div class="section-label">Project Reference</div>
                    <div class="client-name">{{ $invoice->project->name }}</div>
                    <div class="client-info">Project Code: {{ $invoice->project->code }}</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Line Items --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Item / Description</th>
                <th style="width: 15%;" class="text-center">Quantity</th>
                <th style="width: 15%;" class="text-right">Unit Price</th>
                <th style="width: 20%;" class="text-right">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->item_name }}</strong>
                        @if($item->description)
                            <br><span style="color: #64748b; font-size: 10px;">{{ $item->description }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ (float)$item->quantity }}</td>
                    <td class="text-right">{{ currency_format($item->unit_price) }}</td>
                    <td class="text-right"><strong>{{ currency_format($item->total_price) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summary --}}
    <table class="totals-table">
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">{{ currency_format($invoice->subtotal) }}</td>
        </tr>
        @if($invoice->discount > 0)
            <tr>
                <td style="color: #dc2626;">Discount:</td>
                <td class="text-right" style="color: #dc2626;">-{{ currency_format($invoice->discount) }}</td>
            </tr>
        @endif
        @if($invoice->tax > 0)
            <tr>
                <td>Tax / VAT:</td>
                <td class="text-right">+{{ currency_format($invoice->tax) }}</td>
            </tr>
        @endif
        <tr class="total-row">
            <td>Grand Total:</td>
            <td class="text-right">{{ currency_format($invoice->total_amount) }}</td>
        </tr>
        <tr>
            <td style="color: #16a34a; font-weight: bold;">Amount Paid:</td>
            <td class="text-right" style="color: #16a34a; font-weight: bold;">{{ currency_format($invoice->paid_amount) }}</td>
        </tr>
        <tr>
            <td style="color: #ea580c; font-weight: bold;">Balance Due:</td>
            <td class="text-right" style="color: #ea580c; font-weight: bold;">{{ currency_format($invoice->due_amount) }}</td>
        </tr>
    </table>

    {{-- Notes & Terms --}}
    @if($invoice->notes || $invoice->terms)
        <div class="notes-box">
            @if($invoice->notes)
                <strong>Payment Notes:</strong> {{ $invoice->notes }}<br>
            @endif
            @if($invoice->terms)
                <strong>Terms & Conditions:</strong> {{ $invoice->terms }}
            @endif
        </div>
    @endif

</body>
</html>
