<?php

namespace Database\Seeders;

use App\Enums\ClientStatusEnum;
use App\Models\City;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\ClientNote;
use App\Models\Country;
use App\Models\Currency;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first() ?? User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@erp.test',
        ]);
        $adminId = $admin->id;

        $usd = Currency::where('code', 'USD')->first();
        $bdt = Currency::where('code', 'BDT')->first();
        $eur = Currency::where('code', 'EUR')->first();
        $gbp = Currency::where('code', 'GBP')->first();

        $us = Country::where('iso2', 'US')->first() ?? Country::first();
        $bd = Country::where('iso2', 'BD')->first() ?? Country::first();
        $gb = Country::where('iso2', 'GB')->first() ?? Country::first();

        $clientsData = [
            [
                'company_name' => 'Nexus Software Labs',
                'contact_name' => 'Alexander Hayes',
                'email' => 'alex@nexuslabs.io',
                'phone' => '+1 (415) 890-1234',
                'website' => 'https://nexuslabs.io',
                'country_id' => $us?->id,
                'address' => '500 Howard Street, Suite 400, San Francisco, CA 94105',
                'industry' => 'Cloud Infrastructure & SaaS',
                'currency_id' => $usd?->id,
                'status' => ClientStatusEnum::ACTIVE,
                'notes' => 'Key enterprise account. Long-term contract renewed in Q1.',
                'contacts' => [
                    ['name' => 'Alexander Hayes', 'email' => 'alex@nexuslabs.io', 'phone' => '+1 (415) 890-1234', 'designation' => 'Chief Technology Officer', 'is_primary' => true],
                    ['name' => 'Sarah Connor', 'email' => 'sarah.c@nexuslabs.io', 'phone' => '+1 (415) 890-5678', 'designation' => 'VP of Engineering', 'is_primary' => false],
                ],
                'notes_list' => [
                    'Kickoff meeting completed successfully. Monthly sprint reviews scheduled for every second Tuesday.',
                    'Client requested additional billing details for cross-border tax compliance.',
                ],
            ],
            [
                'company_name' => 'Quantum Health Systems',
                'contact_name' => 'Dr. Elena Rostova',
                'email' => 'contact@quantumhealth.com',
                'phone' => '+1 (617) 555-0142',
                'website' => 'https://quantumhealth.com',
                'country_id' => $us?->id,
                'address' => '200 Longwood Ave, Boston, MA 02115',
                'industry' => 'Healthcare & Telemedicine',
                'currency_id' => $usd?->id,
                'status' => ClientStatusEnum::ACTIVE,
                'notes' => 'HIPAA compliant EHR integration project.',
                'contacts' => [
                    ['name' => 'Dr. Elena Rostova', 'email' => 'contact@quantumhealth.com', 'phone' => '+1 (617) 555-0142', 'designation' => 'Director of Medical Informatics', 'is_primary' => true],
                    ['name' => 'Marcus Vance', 'email' => 'mvance@quantumhealth.com', 'phone' => '+1 (617) 555-0199', 'designation' => 'Compliance Officer', 'is_primary' => false],
                ],
                'notes_list' => [
                    'Security assessment passed with flying colors. Awaiting final DPA sign-off.',
                ],
            ],
            [
                'company_name' => 'Apex Financial Partners',
                'contact_name' => 'William Sterling',
                'email' => 'info@apexfinancial.co.uk',
                'phone' => '+44 20 7946 0912',
                'website' => 'https://apexfinancial.co.uk',
                'country_id' => $gb?->id,
                'address' => '30 St Mary Axe, London EC3A 8EP',
                'industry' => 'Fintech & Investment Banking',
                'currency_id' => $gbp?->id,
                'status' => ClientStatusEnum::ACTIVE,
                'notes' => 'Portfolio analytics dashboard integration.',
                'contacts' => [
                    ['name' => 'William Sterling', 'email' => 'info@apexfinancial.co.uk', 'phone' => '+44 20 7946 0912', 'designation' => 'Managing Director', 'is_primary' => true],
                    ['name' => 'Claire Davenport', 'email' => 'claire.d@apexfinancial.co.uk', 'phone' => '+44 20 7946 0888', 'designation' => 'Operations Lead', 'is_primary' => false],
                ],
                'notes_list' => [
                    'API authentication keys generated and shared via encrypted vault.',
                ],
            ],
            [
                'company_name' => 'Bengal Tech Ventures',
                'contact_name' => 'Tanvir Ahmed',
                'email' => 'tanvir@bengaltech.com.bd',
                'phone' => '+880 1711-223344',
                'website' => 'https://bengaltech.com.bd',
                'country_id' => $bd?->id,
                'address' => 'Plot 15, Road 27, Block J, Banani, Dhaka 1213',
                'industry' => 'E-Commerce & Logistics',
                'currency_id' => $bdt?->id,
                'status' => ClientStatusEnum::ACTIVE,
                'notes' => 'Local logistics and automated order fulfillment platform.',
                'contacts' => [
                    ['name' => 'Tanvir Ahmed', 'email' => 'tanvir@bengaltech.com.bd', 'phone' => '+880 1711-223344', 'designation' => 'CEO & Founder', 'is_primary' => true],
                    ['name' => 'Fahim Rahman', 'email' => 'fahim@bengaltech.com.bd', 'phone' => '+880 1819-556677', 'designation' => 'Head of Product', 'is_primary' => false],
                ],
                'notes_list' => [
                    'Payment gateway integration testing in progress for bKash and Nagad.',
                ],
            ],
            [
                'company_name' => 'Solaris Clean Energy',
                'contact_name' => 'Julian Brandt',
                'email' => 'j.brandt@solaris-energy.de',
                'phone' => '+49 30 22730000',
                'website' => 'https://solaris-energy.de',
                'country_id' => $us?->id,
                'address' => 'Friedrichstraße 43, 10117 Berlin, Germany',
                'industry' => 'Renewable Energy & IoT',
                'currency_id' => $eur?->id,
                'status' => ClientStatusEnum::LEAD,
                'notes' => 'Prospective lead from Munich Tech Expo. Interested in IoT telemetry reporting.',
                'contacts' => [
                    ['name' => 'Julian Brandt', 'email' => 'j.brandt@solaris-energy.de', 'phone' => '+49 30 22730000', 'designation' => 'VP Business Development', 'is_primary' => true],
                ],
                'notes_list' => [
                    'Demo scheduled for next Monday at 3:00 PM CET.',
                ],
            ],
            [
                'company_name' => 'Aether Digital Media',
                'contact_name' => 'Chloe Bennett',
                'email' => 'chloe@aethermedia.io',
                'phone' => '+1 (312) 555-0188',
                'website' => 'https://aethermedia.io',
                'country_id' => $us?->id,
                'address' => '233 S Wacker Dr, Chicago, IL 60606',
                'industry' => 'Digital Marketing & Content',
                'currency_id' => $usd?->id,
                'status' => ClientStatusEnum::ACTIVE,
                'notes' => 'Automated video distribution and analytics pipeline.',
                'contacts' => [
                    ['name' => 'Chloe Bennett', 'email' => 'chloe@aethermedia.io', 'phone' => '+1 (312) 555-0188', 'designation' => 'Head of Creative Operations', 'is_primary' => true],
                ],
                'notes_list' => [
                    'Brand guidelines received and imported into assets library.',
                ],
            ],
            [
                'company_name' => 'Vanguard Logistics Corp',
                'contact_name' => 'Robert MacIntyre',
                'email' => 'robert@vanguardlogistics.com',
                'phone' => '+1 (206) 555-0164',
                'website' => 'https://vanguardlogistics.com',
                'country_id' => $us?->id,
                'address' => '1000 4th Ave, Seattle, WA 98104',
                'industry' => 'Supply Chain & Transportation',
                'currency_id' => $usd?->id,
                'status' => ClientStatusEnum::INACTIVE,
                'notes' => 'Contract paused temporarily due to seasonal supply chain realignment.',
                'contacts' => [
                    ['name' => 'Robert MacIntyre', 'email' => 'robert@vanguardlogistics.com', 'phone' => '+1 (206) 555-0164', 'designation' => 'Director of Logistics', 'is_primary' => true],
                ],
                'notes_list' => [
                    'Account put on hold until Q3 budget cycle.',
                ],
            ],
            [
                'company_name' => 'Summit AI Research',
                'contact_name' => 'Maya Patel',
                'email' => 'maya@summit-ai.org',
                'phone' => '+1 (512) 555-0177',
                'website' => 'https://summit-ai.org',
                'country_id' => $us?->id,
                'address' => '500 W 2nd St, Austin, TX 78701',
                'industry' => 'Artificial Intelligence & Robotics',
                'currency_id' => $usd?->id,
                'status' => ClientStatusEnum::LEAD,
                'notes' => 'Exploratory discussions on custom ML training workflow integration.',
                'contacts' => [
                    ['name' => 'Maya Patel', 'email' => 'maya@summit-ai.org', 'phone' => '+1 (512) 555-0177', 'designation' => 'Research Lead', 'is_primary' => true],
                ],
                'notes_list' => [
                    'Sent initial technical capabilities whitepaper.',
                ],
            ],
        ];

        foreach ($clientsData as $data) {
            $contacts = $data['contacts'] ?? [];
            $notes = $data['notes_list'] ?? [];
            unset($data['contacts'], $data['notes_list']);

            // Find matching state/city if country exists
            if (!empty($data['country_id'])) {
                $state = State::where('country_id', $data['country_id'])->first();
                if ($state) {
                    $data['state_id'] = $state->id;
                    $city = City::where('state_id', $state->id)->first();
                    if ($city) {
                        $data['city_id'] = $city->id;
                    }
                }
            }

            if (empty($data['code'])) {
                $data['code'] = Client::generateCode();
            }

            $client = Client::create($data);

            foreach ($contacts as $contactData) {
                ClientContact::create([
                    'client_id' => $client->id,
                    'name' => $contactData['name'],
                    'email' => $contactData['email'],
                    'phone' => $contactData['phone'] ?? null,
                    'designation' => $contactData['designation'] ?? null,
                    'is_primary' => $contactData['is_primary'] ?? false,
                ]);
            }

            foreach ($notes as $noteText) {
                ClientNote::create([
                    'client_id' => $client->id,
                    'user_id' => $adminId,
                    'note' => $noteText,
                ]);
            }
        }
    }
}
