<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Contact::exists()) {
            $this->command->info('Contacts already seeded, skipping...');
            return;
        }

        $users = User::all()->keyBy('email');

        if ($users->isEmpty()) {
            $this->command->warn('No users found, skipping contact seeding...');
            return;
        }

        $morphType = (new User())->getMorphClass();

        foreach ($this->getContacts($morphType, $users) as $contact) {
            Contact::create($contact);
        }
    }

    /**
     * Get the predefined contact records to seed.
     *
     * @param  string  $morphType
     * @param  \Illuminate\Support\Collection<string, User>  $users
     * @return array<int, array<string, string|int|null>>
     */
    private function getContacts(string $morphType, \Illuminate\Support\Collection $users): array
    {
        return [
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['superadmin@example.com']->id,
                'phone' => '+44 20 7946 0958',
                'email' => 'james.hartley@example.co.uk',
                'address' => '12 Baker Street',
                'city' => 'London',
                'postal_code' => 'W1U 3BH',
                'country' => 'United Kingdom',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['admin@example.com']->id,
                'phone' => '+44 161 496 0234',
                'email' => 'sarah.thornton@example.co.uk',
                'address' => '45 Deansgate',
                'city' => 'Manchester',
                'postal_code' => 'M3 2AY',
                'country' => 'United Kingdom',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['john.admin@example.com']->id,
                'phone' => '+44 131 496 1122',
                'email' => 'robert.mcdonald@example.co.uk',
                'address' => '8 Princes Street',
                'city' => 'Edinburgh',
                'postal_code' => 'EH2 2BY',
                'country' => 'United Kingdom',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['manager@example.com']->id,
                'phone' => '+44 29 2049 6000',
                'email' => 'emily.jones@example.co.uk',
                'address' => '22 St Mary Street',
                'city' => 'Cardiff',
                'postal_code' => 'CF10 1AB',
                'country' => 'United Kingdom',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['editor@example.com']->id,
                'phone' => '+44 121 496 0871',
                'email' => 'david.price@example.co.uk',
                'address' => '67 Colmore Row',
                'city' => 'Birmingham',
                'postal_code' => 'B3 2AA',
                'country' => 'United Kingdom',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['moderator@example.com']->id,
                'phone' => '+1 212 555 0172',
                'email' => 'michael.roberts@example.com',
                'address' => '350 Fifth Avenue',
                'city' => 'New York',
                'postal_code' => '10118',
                'country' => 'United States',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['support@example.com']->id,
                'phone' => '+1 415 555 0193',
                'email' => 'jessica.walker@example.com',
                'address' => '1 Market Street',
                'city' => 'San Francisco',
                'postal_code' => '94105',
                'country' => 'United States',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['analyst@example.com']->id,
                'phone' => '+1 312 555 0148',
                'email' => 'daniel.harris@example.com',
                'address' => '233 South Wacker Drive',
                'city' => 'Chicago',
                'postal_code' => '60606',
                'country' => 'United States',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['viewer@example.com']->id,
                'phone' => '+49 30 12345678',
                'email' => 'anna.mueller@example.de',
                'address' => 'Unter den Linden 1',
                'city' => 'Berlin',
                'postal_code' => '10117',
                'country' => 'Germany',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['user@example.com']->id,
                'phone' => '+33 1 42 86 82 00',
                'email' => 'pierre.dupont@example.fr',
                'address' => '15 Rue de Rivoli',
                'city' => 'Paris',
                'postal_code' => '75001',
                'country' => 'France',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['jane.smith@example.com']->id,
                'phone' => '+61 2 9876 5432',
                'email' => 'olivia.nguyen@example.com.au',
                'address' => '1 Martin Place',
                'city' => 'Sydney',
                'postal_code' => '2000',
                'country' => 'Australia',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['bob.johnson@example.com']->id,
                'phone' => '+1 604 555 0167',
                'email' => 'liam.campbell@example.ca',
                'address' => '200 Burrard Street',
                'city' => 'Vancouver',
                'postal_code' => 'V6C 3L6',
                'country' => 'Canada',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['writer@example.com']->id,
                'phone' => null,
                'email' => 'sophie.martin@example.fr',
                'address' => '10 Place Bellecour',
                'city' => 'Lyon',
                'postal_code' => '69002',
                'country' => 'France',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['cs@example.com']->id,
                'phone' => '+44 113 496 0345',
                'email' => null,
                'address' => '1 City Square',
                'city' => 'Leeds',
                'postal_code' => 'LS1 2ES',
                'country' => 'United Kingdom',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['data@example.com']->id,
                'phone' => '+44 28 9024 6200',
                'email' => 'conor.obryan@example.co.uk',
                'address' => '3 Donegall Square North',
                'city' => 'Belfast',
                'postal_code' => 'BT1 5GS',
                'country' => 'United Kingdom',
            ],
        ];
    }
}
