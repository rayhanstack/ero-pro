<?php

namespace App\Services\Client;

use App\Enums\ClientStatusEnum;
use App\Helpers\MediaHelper;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\ClientNote;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ClientService
{
    /**
     * Get paginated clients with eager loading and filters.
     */
    public function getClients(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Client::with(['country', 'currency', 'contacts'])->latest();

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->status($filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get dashboard summary statistics.
     */
    public function getStats(): array
    {
        $now = Carbon::now();

        return [
            'total' => Client::count(),
            'active' => Client::where('status', ClientStatusEnum::ACTIVE)->count(),
            'new_this_month' => Client::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),
            'total_revenue' => 0.00, // Placeholder from future finance module
        ];
    }

    /**
     * Create a new client with optional logo upload and primary contact.
     */
    public function create(array $data): Client
    {
        return DB::transaction(function () use ($data) {
            if (! empty($data['logo']) && $data['logo'] instanceof UploadedFile) {
                $data['logo'] = MediaHelper::upload($data['logo'], 'clients');
            }

            if (empty($data['code'])) {
                $data['code'] = Client::generateCode();
            }

            $client = Client::create($data);

            // Create initial primary contact record
            if (! empty($data['contact_name'])) {
                $client->contacts()->create([
                    'name' => $data['contact_name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'designation' => 'Primary Contact',
                    'is_primary' => true,
                ]);
            }

            return $client->fresh(['country', 'state', 'city', 'currency', 'contacts']);
        });
    }

    /**
     * Update an existing client.
     */
    public function update(Client $client, array $data): Client
    {
        return DB::transaction(function () use ($client, $data) {
            if (! empty($data['logo']) && $data['logo'] instanceof UploadedFile) {
                if ($client->logo) {
                    MediaHelper::delete($client->logo);
                }
                $data['logo'] = MediaHelper::upload($data['logo'], 'clients');
            } else {
                unset($data['logo']);
            }

            $client->update($data);

            return $client->fresh(['country', 'state', 'city', 'currency', 'contacts']);
        });
    }

    /**
     * Soft delete a client.
     */
    public function delete(Client $client): bool
    {
        return (bool) $client->delete();
    }

    /**
     * Restore a soft-deleted client.
     */
    public function restore(int $id): bool
    {
        $client = Client::onlyTrashed()->findOrFail($id);

        return (bool) $client->restore();
    }

    /**
     * Add an additional contact person for a client.
     */
    public function addContact(Client $client, array $data): ClientContact
    {
        if (! empty($data['is_primary'])) {
            $client->contacts()->where('is_primary', true)->update(['is_primary' => false]);
        }

        return $client->contacts()->create($data);
    }

    /**
     * Delete a contact person.
     */
    public function deleteContact(ClientContact $contact): bool
    {
        return (bool) $contact->delete();
    }

    /**
     * Add a note for a client.
     */
    public function addNote(Client $client, int $userId, string $note): ClientNote
    {
        return $client->clientNotes()->create([
            'user_id' => $userId,
            'note' => $note,
        ]);
    }

    /**
     * Delete a client note.
     */
    public function deleteNote(ClientNote $note): bool
    {
        return (bool) $note->delete();
    }
}
