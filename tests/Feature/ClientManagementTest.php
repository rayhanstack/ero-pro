<?php

namespace Tests\Feature;

use App\Enums\ClientStatusEnum;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\ClientNote;
use App\Models\Country;
use App\Models\Currency;
use App\Models\User;
use Database\Seeders\ClientSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            CountrySeeder::class,
            CurrencySeeder::class,
            PermissionSeeder::class,
            ClientSeeder::class,
        ]);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');

        $this->employee = User::factory()->create();
        $this->employee->assignRole('Employee');
    }

    public function test_clients_index_page_can_be_rendered_in_grid_and_list_modes(): void
    {
        // Grid mode
        $response = $this->actingAs($this->superAdmin)->get(route('clients.index', ['view' => 'grid']));
        $response->assertStatus(200);
        $response->assertSee('Clients');
        $response->assertSee('Nexus Software Labs');
        $response->assertSee('Total Clients');
        $response->assertSee('Active Clients');
        $response->assertSee('New This Month');
        $response->assertSee('Total Revenue');

        // List mode
        $listResponse = $this->actingAs($this->superAdmin)->get(route('clients.index', ['view' => 'list']));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Nexus Software Labs');
    }

    public function test_legacy_client_route_redirects_or_renders_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(url('/client'));
        $response->assertStatus(200);
        $response->assertSee('Clients');
    }

    public function test_clients_index_can_be_filtered_by_search_and_status(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('clients.index', [
            'search' => 'Quantum',
            'status' => 'active',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Quantum Health Systems');
        $response->assertDontSee('Nexus Software Labs');
    }

    public function test_client_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('clients.create'));

        $response->assertStatus(200);
        $response->assertSee('Add New Client');
        $response->assertSee('Company Information');
    }

    public function test_client_can_be_created_with_auto_generated_code_and_primary_contact(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('client-logo.png');

        $currency = Currency::first();
        $country = Country::first();

        $response = $this->actingAs($this->superAdmin)->post(route('clients.store'), [
            'company_name' => 'Horizon Global Tech',
            'contact_name' => 'Emma Watson',
            'email' => 'emma@horizonglobal.com',
            'phone' => '+1 (555) 777-8899',
            'website' => 'https://horizonglobal.com',
            'country_id' => $country?->id,
            'address' => '789 Innovation Way',
            'industry' => 'Cybersecurity',
            'currency_id' => $currency?->id,
            'status' => 'active',
            'notes' => 'New flagship enterprise partnership.',
            'logo' => $file,
        ]);

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('success');

        $client = Client::where('company_name', 'Horizon Global Tech')->first();
        $this->assertNotNull($client);
        $this->assertMatchesRegularExpression('/^CLT-\d{4}$/', $client->code);
        $this->assertEquals('Cybersecurity', $client->industry);
        $this->assertEquals(ClientStatusEnum::ACTIVE, $client->status);
        $this->assertNotNull($client->logo);

        // Assert primary contact was automatically created
        $this->assertDatabaseHas('client_contacts', [
            'client_id' => $client->id,
            'name' => 'Emma Watson',
            'email' => 'emma@horizonglobal.com',
            'is_primary' => true,
        ]);
    }

    public function test_client_show_page_renders_tabs(): void
    {
        $client = Client::first();

        $response = $this->actingAs($this->superAdmin)->get(route('clients.show', $client));

        $response->assertStatus(200);
        $response->assertSee($client->company_name);
        $response->assertSee($client->code);
        $response->assertSee('Overview');
        $response->assertSee('Contacts');
        $response->assertSee('Projects');
        $response->assertSee('Notes');
        $response->assertSee('Invoices & Payments');
    }

    public function test_client_edit_page_can_be_rendered(): void
    {
        $client = Client::first();

        $response = $this->actingAs($this->superAdmin)->get(route('clients.edit', $client));

        $response->assertStatus(200);
        $response->assertSee('Edit Client');
        $response->assertSee($client->company_name);
    }

    public function test_client_can_be_updated(): void
    {
        $client = Client::first();

        $response = $this->actingAs($this->superAdmin)->put(route('clients.update', $client), [
            'company_name' => 'Updated Client Name Corp',
            'contact_name' => 'Updated Contact Name',
            'email' => 'updated@client.com',
            'phone' => '+1 888 999 0000',
            'website' => 'https://updatedclient.com',
            'industry' => 'Advanced Robotics',
            'status' => 'inactive',
            'notes' => 'Updated notes.',
        ]);

        $response->assertRedirect(route('clients.show', $client));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'company_name' => 'Updated Client Name Corp',
            'contact_name' => 'Updated Contact Name',
            'email' => 'updated@client.com',
            'status' => 'inactive',
        ]);
    }

    public function test_client_can_be_soft_deleted_and_restored(): void
    {
        $client = Client::create([
            'company_name' => 'Deletable Client',
            'contact_name' => 'John Doe',
            'email' => 'deletable@test.com',
            'status' => ClientStatusEnum::ACTIVE,
        ]);

        // Soft delete
        $response = $this->actingAs($this->superAdmin)->delete(route('clients.destroy', $client));
        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('clients', ['id' => $client->id]);

        // Restore
        $restoreResponse = $this->actingAs($this->superAdmin)->post(route('clients.restore', $client->id));
        $restoreResponse->assertRedirect(route('clients.index'));
        $restoreResponse->assertSessionHas('success');

        $this->assertNotSoftDeleted('clients', ['id' => $client->id]);
    }

    public function test_contacts_can_be_added_and_deleted(): void
    {
        $client = Client::first();

        // Add contact
        $response = $this->actingAs($this->superAdmin)->post(route('clients.contacts.store', $client), [
            'name' => 'New Contact Person',
            'email' => 'new.contact@client.com',
            'phone' => '+1 234 567 8900',
            'designation' => 'Lead Architect',
            'is_primary' => false,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('client_contacts', [
            'client_id' => $client->id,
            'name' => 'New Contact Person',
            'email' => 'new.contact@client.com',
        ]);

        $contact = ClientContact::where('email', 'new.contact@client.com')->first();

        // Delete contact
        $delResponse = $this->actingAs($this->superAdmin)->delete(route('clients.contacts.destroy', $contact));
        $delResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('client_contacts', ['id' => $contact->id]);
    }

    public function test_notes_can_be_added_and_deleted(): void
    {
        $client = Client::first();

        // Add note
        $response = $this->actingAs($this->superAdmin)->post(route('clients.notes.store', $client), [
            'note' => 'This is a test discussion note logged by admin.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('client_notes', [
            'client_id' => $client->id,
            'note' => 'This is a test discussion note logged by admin.',
        ]);

        $note = ClientNote::where('note', 'This is a test discussion note logged by admin.')->first();

        // Delete note
        $delResponse = $this->actingAs($this->superAdmin)->delete(route('clients.notes.destroy', $note));
        $delResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('client_notes', ['id' => $note->id]);
    }

    public function test_unauthorized_user_cannot_access_client_module(): void
    {
        $response = $this->actingAs($this->employee)->get(route('clients.index'));
        $response->assertStatus(403);
    }
}
