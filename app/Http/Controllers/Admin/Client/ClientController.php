<?php

namespace App\Http\Controllers\Admin\Client;

use App\Enums\ClientStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientContactRequest;
use App\Http\Requests\Client\StoreClientNoteRequest;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\City;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\ClientNote;
use App\Models\Country;
use App\Models\Currency;
use App\Models\State;
use App\Services\Client\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(
        protected ClientService $clientService
    ) {}

    /**
     * Display a listing of clients with grid/list toggle and stat cards.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status']);
        $viewMode = $request->input('view', 'grid');
        $perPage = $viewMode === 'list' ? 15 : 12;

        $clients = $this->clientService->getClients($filters, $perPage);
        $stats = $this->clientService->getStats();
        $statuses = ClientStatusEnum::cases();
        $countries = Country::all();
        $currencies = Currency::all();

        return view('admin.client.index', compact(
            'clients',
            'stats',
            'filters',
            'viewMode',
            'statuses',
            'countries',
            'currencies'
        ));
    }

    /**
     * Show the form for creating a new client.
     */
    public function create(): View
    {
        $countries = Country::all();
        $currencies = Currency::all();
        $statuses = ClientStatusEnum::cases();

        return view('admin.client.create', compact('countries', 'currencies', 'statuses'));
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(StoreClientRequest $request): RedirectResponse|JsonResponse
    {
        $client = $this->clientService->create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Client created successfully.'),
                'data' => $client,
            ]);
        }

        return redirect()->route('clients.index')->with('success', _trans('common.Client created successfully.'));
    }

    /**
     * Display the specified client profile and tabs.
     */
    public function show(Client $client): View
    {
        $client->load([
            'country',
            'state',
            'city',
            'currency',
            'contacts',
            'clientNotes.user',
            'projects',
            'invoices.project',
            'invoices.payments.account',
        ]);

        return view('admin.client.show', compact('client'));
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client): View
    {
        $client->load(['country', 'state', 'city', 'currency']);
        $countries = Country::all();
        $states = $client->country_id ? State::where('country_id', $client->country_id)->get() : collect();
        $cities = $client->state_id ? City::where('state_id', $client->state_id)->get() : collect();
        $currencies = Currency::all();
        $statuses = ClientStatusEnum::cases();

        return view('admin.client.edit', compact('client', 'countries', 'states', 'cities', 'currencies', 'statuses'));
    }

    /**
     * Update the specified client in storage.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse|JsonResponse
    {
        $this->clientService->update($client, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Client updated successfully.'),
                'data' => $client,
            ]);
        }

        return redirect()->route('clients.show', $client)->with('success', _trans('common.Client updated successfully.'));
    }

    /**
     * Remove the specified client from storage (Soft Delete).
     */
    public function destroy(Client $client): RedirectResponse|JsonResponse
    {
        $this->clientService->delete($client);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Client deleted successfully.'),
            ]);
        }

        return redirect()->route('clients.index')->with('success', _trans('common.Client deleted successfully.'));
    }

    /**
     * Restore a soft-deleted client.
     */
    public function restore(int $id): RedirectResponse
    {
        $this->clientService->restore($id);

        return redirect()->route('clients.index')->with('success', _trans('common.Client restored successfully.'));
    }

    /**
     * Add a contact person for the client.
     */
    public function addContact(StoreClientContactRequest $request, Client $client): RedirectResponse|JsonResponse
    {
        $contact = $this->clientService->addContact($client, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Contact added successfully.'),
                'data' => $contact,
            ]);
        }

        return back()->with('success', _trans('common.Contact added successfully.'));
    }

    /**
     * Delete a contact person.
     */
    public function deleteContact(ClientContact $contact): RedirectResponse|JsonResponse
    {
        $this->clientService->deleteContact($contact);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Contact deleted successfully.'),
            ]);
        }

        return back()->with('success', _trans('common.Contact deleted successfully.'));
    }

    /**
     * Add a note for the client.
     */
    public function addNote(StoreClientNoteRequest $request, Client $client): RedirectResponse|JsonResponse
    {
        $note = $this->clientService->addNote($client, Auth::id(), $request->input('note'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Note added successfully.'),
                'data' => $note,
            ]);
        }

        return back()->with('success', _trans('common.Note added successfully.'));
    }

    /**
     * Delete a note.
     */
    public function deleteNote(ClientNote $note): RedirectResponse|JsonResponse
    {
        $this->clientService->deleteNote($note);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Note deleted successfully.'),
            ]);
        }

        return back()->with('success', _trans('common.Note deleted successfully.'));
    }
}
