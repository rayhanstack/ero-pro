<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Enums\InvoiceStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreInvoicePaymentRequest;
use App\Http\Requests\Finance\StoreInvoiceRequest;
use App\Http\Requests\Finance\UpdateInvoiceRequest;
use App\Models\Account;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Project;
use App\Services\Finance\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'client_id', 'project_id', 'status', 'start_date', 'end_date']);
        $invoices = $this->invoiceService->getPaginatedInvoices($filters, 15);
        $stats = $this->invoiceService->getInvoiceStats();

        $clients = Client::active()->orderBy('company_name')->get();
        $projects = Project::orderBy('title')->get();
        $statuses = InvoiceStatusEnum::cases();

        $title = _trans('common.Invoices');

        return view('admin.finance.invoices.index', compact(
            'title',
            'invoices',
            'stats',
            'filters',
            'clients',
            'projects',
            'statuses'
        ));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(Request $request): View
    {
        $clients = Client::active()->orderBy('company_name')->get();
        $projects = Project::orderBy('title')->get();
        $currencies = Currency::all();
        $statuses = InvoiceStatusEnum::cases();
        $selectedClientId = $request->get('client_id');
        $selectedProjectId = $request->get('project_id');

        $title = _trans('common.Create Invoice');

        return view('admin.finance.invoices.create', compact(
            'title',
            'clients',
            'projects',
            'currencies',
            'statuses',
            'selectedClientId',
            'selectedProjectId'
        ));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(StoreInvoiceRequest $request): RedirectResponse|JsonResponse
    {
        $invoice = $this->invoiceService->createInvoice($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Invoice created successfully.'),
                'data' => $invoice,
                'redirect' => route('finance.invoices.show', $invoice),
            ]);
        }

        return redirect()->route('finance.invoices.show', $invoice)
            ->with('success', _trans('common.Invoice created successfully.'));
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->loadMissing([
            'client.country',
            'client.currency',
            'project',
            'items',
            'payments.account',
            'payments.creator',
            'creator',
        ]);

        $accounts = Account::active()->orderBy('name')->get();
        $title = _trans('common.Invoice :number', ['number' => $invoice->invoice_number]);

        return view('admin.finance.invoices.show', compact('title', 'invoice', 'accounts'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice): View
    {
        $invoice->loadMissing(['client', 'project', 'items']);
        $clients = Client::active()->orderBy('company_name')->get();
        $projects = Project::orderBy('title')->get();
        $currencies = Currency::all();
        $statuses = InvoiceStatusEnum::cases();

        $title = _trans('common.Edit Invoice :number', ['number' => $invoice->invoice_number]);

        return view('admin.finance.invoices.edit', compact(
            'title',
            'invoice',
            'clients',
            'projects',
            'currencies',
            'statuses'
        ));
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse|JsonResponse
    {
        $updated = $this->invoiceService->updateInvoice($invoice, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Invoice updated successfully.'),
                'data' => $updated,
                'redirect' => route('finance.invoices.show', $invoice),
            ]);
        }

        return redirect()->route('finance.invoices.show', $invoice)
            ->with('success', _trans('common.Invoice updated successfully.'));
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(Request $request, Invoice $invoice): RedirectResponse|JsonResponse
    {
        try {
            $this->invoiceService->deleteInvoice($invoice);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => _trans('common.Invoice deleted successfully.'),
                ]);
            }

            return redirect()->route('finance.invoices.index')
                ->with('success', _trans('common.Invoice deleted successfully.'));
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Record a payment on the specified invoice.
     */
    public function recordPayment(StoreInvoicePaymentRequest $request, Invoice $invoice): RedirectResponse|JsonResponse
    {
        try {
            $payment = $this->invoiceService->recordPayment($invoice, $request->validated());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => _trans('common.Payment of :amount recorded successfully.', ['amount' => currency_format($payment->amount)]),
                    'data' => $payment,
                ]);
            }

            return redirect()->route('finance.invoices.show', $invoice)
                ->with('success', _trans('common.Payment of :amount recorded successfully.', ['amount' => currency_format($payment->amount)]));
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download PDF version of the invoice.
     */
    public function downloadPdf(Invoice $invoice): Response
    {
        return $this->invoiceService->generatePdf($invoice);
    }

    /**
     * View PDF version of the invoice.
     */
    public function pdf(Invoice $invoice): Response
    {
        return $this->downloadPdf($invoice);
    }
}
