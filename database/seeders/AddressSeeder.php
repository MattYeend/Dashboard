<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Address::exists()) {
            $this->command->info('Addresses already seeded, skipping...');

            return;
        }

        $users = User::all()->keyBy('email');

        if ($users->isEmpty()) {
            $this->command->warn('No users found, skipping address seeding...');

            return;
        }

        $userMorphType = (new User)->getMorphClass();

        $contacts = Contact::where('contactable_type', $userMorphType)
            ->get()
            ->keyBy('contactable_id');

        if ($contacts->isEmpty()) {
            $this->command->warn('No contacts found, skipping address seeding...');

            return;
        }

        $morphType = (new Contact)->getMorphClass();

        foreach ($this->getAddresses($morphType, $users, $contacts) as $address) {
            Address::create($address);
        }
    }

    /**
     * Get the predefined address records to seed.
     *
     * @param  Collection<string, User>  $users
     * @param  Collection<int, Contact>  $contacts
     * @return array<int, array<string, string|int|bool|null>>
     */
    private function getAddresses(string $morphType, Collection $users, Collection $contacts): array
    {
        return [
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['superadmin@example.com']->id]->id,
                'address_line_one' => '12 Baker Street',
                'address_line_two' => null,
                'town' => null,
                'city' => 'London',
                'county' => 'Greater London',
                'postcode' => 'W1U 3BH',
                'country' => 'United Kingdom',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['admin@example.com']->id]->id,
                'address_line_one' => '45 Deansgate',
                'address_line_two' => null,
                'town' => null,
                'city' => 'Manchester',
                'county' => 'Greater Manchester',
                'postcode' => 'M3 2AY',
                'country' => 'United Kingdom',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['john.admin@example.com']->id]->id,
                'address_line_one' => '8 Princes Street',
                'address_line_two' => null,
                'town' => null,
                'city' => 'Edinburgh',
                'county' => null,
                'postcode' => 'EH2 2BY',
                'country' => 'United Kingdom',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['manager@example.com']->id]->id,
                'address_line_one' => '22 St Mary Street',
                'address_line_two' => null,
                'town' => null,
                'city' => 'Cardiff',
                'county' => null,
                'postcode' => 'CF10 1AB',
                'country' => 'United Kingdom',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['editor@example.com']->id]->id,
                'address_line_one' => '67 Colmore Row',
                'address_line_two' => null,
                'town' => null,
                'city' => 'Birmingham',
                'county' => 'West Midlands',
                'postcode' => 'B3 2AA',
                'country' => 'United Kingdom',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['moderator@example.com']->id]->id,
                'address_line_one' => '350 Fifth Avenue',
                'address_line_two' => null,
                'town' => null,
                'city' => 'New York',
                'county' => null,
                'postcode' => '10118',
                'country' => 'United States',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['support@example.com']->id]->id,
                'address_line_one' => '1 Market Street',
                'address_line_two' => null,
                'town' => null,
                'city' => 'San Francisco',
                'county' => null,
                'postcode' => '94105',
                'country' => 'United States',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['analyst@example.com']->id]->id,
                'address_line_one' => '233 South Wacker Drive',
                'address_line_two' => null,
                'town' => null,
                'city' => 'Chicago',
                'county' => null,
                'postcode' => '60606',
                'country' => 'United States',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['viewer@example.com']->id]->id,
                'address_line_one' => 'Unter den Linden 1',
                'address_line_two' => null,
                'town' => null,
                'city' => 'Berlin',
                'county' => null,
                'postcode' => '10117',
                'country' => 'Germany',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['user@example.com']->id]->id,
                'address_line_one' => '15 Rue de Rivoli',
                'address_line_two' => null,
                'town' => null,
                'city' => 'Paris',
                'county' => null,
                'postcode' => '75001',
                'country' => 'France',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['jane.smith@example.com']->id]->id,
                'address_line_one' => '1 Martin Place',
                'address_line_two' => null,
                'town' => null,
                'city' => 'Sydney',
                'county' => null,
                'postcode' => '2000',
                'country' => 'Australia',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['bob.johnson@example.com']->id]->id,
                'address_line_one' => '200 Burrard Street',
                'address_line_two' => null,
                'town' => null,
                'city' => 'Vancouver',
                'county' => null,
                'postcode' => 'V6C 3L6',
                'country' => 'Canada',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['writer@example.com']->id]->id,
                'address_line_one' => '10 Place Bellecour',
                'address_line_two' => null,
                'town' => null,
                'city' => 'Lyon',
                'county' => null,
                'postcode' => '69002',
                'country' => 'France',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['cs@example.com']->id]->id,
                'address_line_one' => '1 City Square',
                'address_line_two' => null,
                'town' => null,
                'city' => 'Leeds',
                'county' => 'West Yorkshire',
                'postcode' => 'LS1 2ES',
                'country' => 'United Kingdom',
                'is_primary' => true,
            ],
            [
                'addressable_type' => $morphType,
                'addressable_id' => $contacts[$users['data@example.com']->id]->id,
                'address_line_one' => '3 Donegall Square North',
                'address_line_two' => null,
                'town' => null,
                'city' => 'Belfast',
                'county' => null,
                'postcode' => 'BT1 5GS',
                'country' => 'United Kingdom',
                'is_primary' => true,
            ],
        ];
    }
}
