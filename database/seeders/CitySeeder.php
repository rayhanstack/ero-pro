<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = DB::table('countries')->pluck('id', 'iso2');
        $states = DB::table('states')->select('id', 'name', 'country_id')->get()->groupBy('country_id');
        $now = now();

        $cityData = [
            'BD' => [
                'Dhaka' => ['Dhaka City', 'Gazipur', 'Narayanganj', 'Tangail', 'Savar', 'Narsingdi', 'Manikganj', 'Munshiganj'],
                'Chattogram' => ['Chattogram City', 'Cox\'s Bazar', 'Cumilla', 'Feni', 'Brahmanbaria', 'Noakhali', 'Chandpur', 'Rangamati'],
                'Rajshahi' => ['Rajshahi City', 'Bogura', 'Pabna', 'Sirajganj', 'Naogaon', 'Natore', 'Chapai Nawabganj', 'Joypurhat'],
                'Khulna' => ['Khulna City', 'Jashore', 'Kushtia', 'Satkhira', 'Bagerhat', 'Jhenaidah', 'Chuadanga', 'Magura'],
                'Barishal' => ['Barishal City', 'Patuakhali', 'Bhola', 'Pirojpur', 'Jhalokati', 'Barguna'],
                'Sylhet' => ['Sylhet City', 'Moulvibazar', 'Habiganj', 'Sunamganj', 'Sreemangal'],
                'Rangpur' => ['Rangpur City', 'Dinajpur', 'Kurigram', 'Gaibandha', 'Saidpur', 'Nilphamari', 'Thakurgaon', 'Panchagarh'],
                'Mymensingh' => ['Mymensingh City', 'Jamalpur', 'Netrokona', 'Sherpur', 'Muktagacha'],
            ],
            'US' => [
                'California' => ['Los Angeles', 'San Francisco', 'San Diego', 'San Jose', 'Sacramento'],
                'New York' => ['New York City', 'Buffalo', 'Albany', 'Rochester', 'Syracuse'],
                'Texas' => ['Houston', 'Austin', 'Dallas', 'San Antonio', 'Fort Worth'],
                'Florida' => ['Miami', 'Orlando', 'Tampa', 'Jacksonville', 'Tallahassee'],
                'Illinois' => ['Chicago', 'Aurora', 'Naperville', 'Springfield'],
                'Washington' => ['Seattle', 'Spokane', 'Tacoma', 'Vancouver', 'Bellevue'],
            ],
            'GB' => [
                'England' => ['London', 'Manchester', 'Birmingham', 'Liverpool', 'Leeds', 'Bristol'],
                'Scotland' => ['Edinburgh', 'Glasgow', 'Aberdeen', 'Dundee'],
                'Wales' => ['Cardiff', 'Swansea', 'Newport'],
                'Northern Ireland' => ['Belfast', 'Derry', 'Lisburn'],
            ],
            'AE' => [
                'Dubai' => ['Dubai City', 'Deira', 'Jumeirah', 'Bur Dubai', 'Downtown Dubai'],
                'Abu Dhabi' => ['Abu Dhabi City', 'Al Ain', 'Al Dhafra'],
                'Sharjah' => ['Sharjah City', 'Khor Fakkan', 'Kalba'],
                'Ajman' => ['Ajman City', 'Manama', 'Masfout'],
            ],
            'IN' => [
                'West Bengal' => ['Kolkata', 'Siliguri', 'Asansol', 'Howrah', 'Durgapur'],
                'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Nashik', 'Thane'],
                'Delhi' => ['New Delhi', 'North Delhi', 'South Delhi', 'East Delhi'],
                'Karnataka' => ['Bengaluru', 'Mysuru', 'Hubballi', 'Mangaluru'],
                'Tamil Nadu' => ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli'],
            ],
            'CA' => [
                'Ontario' => ['Toronto', 'Ottawa', 'Mississauga', 'Hamilton', 'London'],
                'Quebec' => ['Montreal', 'Quebec City', 'Laval', 'Gatineau'],
                'British Columbia' => ['Vancouver', 'Victoria', 'Surrey', 'Burnaby', 'Richmond'],
                'Alberta' => ['Calgary', 'Edmonton', 'Red Deer', 'Lethbridge'],
            ],
        ];

        $records = [];

        foreach ($cityData as $iso2 => $stateCities) {
            $countryId = $countries[$iso2] ?? null;
            if (!$countryId || !isset($states[$countryId])) {
                continue;
            }

            $stateModels = $states[$countryId]->keyBy('name');

            foreach ($stateCities as $stateName => $cities) {
                $stateId = $stateModels[$stateName]->id ?? null;
                if ($stateId) {
                    foreach ($cities as $cityName) {
                        $records[] = [
                            'name' => $cityName,
                            'state_id' => $stateId,
                            'country_id' => $countryId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }
        }

        if (!empty($records)) {
            // Bulk insert in chunks of 100
            foreach (array_chunk($records, 100) as $chunk) {
                DB::table('cities')->insertOrIgnore($chunk);
            }
        }
    }
}
