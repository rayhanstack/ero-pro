<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
use App\Models\State;
use App\Models\User;
use Database\Seeders\CitySeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\StateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationAjaxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            CurrencySeeder::class,
            LanguageSeeder::class,
        ]);
    }

    public function test_seeders_populate_countries_states_and_cities(): void
    {
        $bd = Country::where('iso2', 'BD')->first();
        $this->assertNotNull($bd);
        $this->assertEquals('Bangladesh', $bd->name);

        $dhakaState = State::where('country_id', $bd->id)->where('name', 'Dhaka')->first();
        $this->assertNotNull($dhakaState);

        $dhakaCity = City::where('state_id', $dhakaState->id)->where('name', 'Dhaka City')->first();
        $this->assertNotNull($dhakaCity);

        // Currencies
        $this->assertDatabaseHas('currencies', ['code' => 'BDT', 'symbol' => '৳']);
        $this->assertDatabaseHas('currencies', ['code' => 'USD', 'symbol' => '$']);
        $this->assertDatabaseHas('currencies', ['code' => 'EUR', 'symbol' => '€']);

        // Languages
        $this->assertDatabaseHas('languages', ['code' => 'en', 'is_default' => 1]);
        $this->assertDatabaseHas('languages', ['code' => 'bn', 'native' => 'বাংলা']);
    }

    public function test_get_states_ajax_endpoint_returns_json(): void
    {
        $user = User::factory()->create();
        $country = Country::where('iso2', 'BD')->firstOrFail();

        $response = $this->actingAs($user)->getJson(route('admin.ajax.states', ['country' => $country->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'country_id'],
            ])
            ->assertJsonFragment(['name' => 'Dhaka'])
            ->assertJsonFragment(['name' => 'Chattogram']);
    }

    public function test_get_cities_ajax_endpoint_returns_json(): void
    {
        $user = User::factory()->create();
        $dhakaState = State::where('name', 'Dhaka')->firstOrFail();

        $response = $this->actingAs($user)->getJson(route('admin.ajax.cities', ['state' => $dhakaState->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'state_id', 'country_id'],
            ])
            ->assertJsonFragment(['name' => 'Dhaka City'])
            ->assertJsonFragment(['name' => 'Gazipur']);
    }

    public function test_factories_create_valid_records(): void
    {
        $country = Country::factory()->create();
        $this->assertDatabaseHas('countries', ['id' => $country->id]);

        $state = State::factory()->create(['country_id' => $country->id]);
        $this->assertDatabaseHas('states', ['id' => $state->id]);

        $city = City::factory()->create(['state_id' => $state->id, 'country_id' => $country->id]);
        $this->assertDatabaseHas('cities', ['id' => $city->id]);

        $currency = Currency::factory()->create();
        $this->assertDatabaseHas('currencies', ['id' => $currency->id]);

        $language = Language::factory()->create();
        $this->assertDatabaseHas('languages', ['id' => $language->id]);
    }
}
