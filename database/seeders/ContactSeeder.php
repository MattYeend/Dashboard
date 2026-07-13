<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

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

        $morphType = (new User)->getMorphClass();

        foreach ($this->getContacts($morphType, $users) as $contact) {
            Contact::create($contact);
        }
    }

    /**
     * Get the predefined contact records to seed.
     *
     * @param  Collection<string, User>  $users
     * @return array<int, array<string, string|int|null>>
     */
    private function getContacts(string $morphType, Collection $users): array
    {
        return [
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['superadmin@example.com']->id,
                'phone' => '+44 20 7946 0958',
                'email' => 'james.hartley@example.co.uk',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['admin@example.com']->id,
                'phone' => '+44 161 496 0234',
                'email' => 'sarah.thornton@example.co.uk',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['john.admin@example.com']->id,
                'phone' => '+44 131 496 1122',
                'email' => 'robert.mcdonald@example.co.uk',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['manager@example.com']->id,
                'phone' => '+44 29 2049 6000',
                'email' => 'emily.jones@example.co.uk',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['editor@example.com']->id,
                'phone' => '+44 121 496 0871',
                'email' => 'david.price@example.co.uk',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['moderator@example.com']->id,
                'phone' => '+1 212 555 0172',
                'email' => 'michael.roberts@example.com',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['support@example.com']->id,
                'phone' => '+1 415 555 0193',
                'email' => 'jessica.walker@example.com',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['analyst@example.com']->id,
                'phone' => '+1 312 555 0148',
                'email' => 'daniel.harris@example.com',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['viewer@example.com']->id,
                'phone' => '+49 30 12345678',
                'email' => 'anna.mueller@example.de',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['user@example.com']->id,
                'phone' => '+33 1 42 86 82 00',
                'email' => 'pierre.dupont@example.fr',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['jane.smith@example.com']->id,
                'phone' => '+61 2 9876 5432',
                'email' => 'olivia.nguyen@example.com.au',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['bob.johnson@example.com']->id,
                'phone' => '+1 604 555 0167',
                'email' => 'liam.campbell@example.ca',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['writer@example.com']->id,
                'phone' => null,
                'email' => 'sophie.martin@example.fr',
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['cs@example.com']->id,
                'phone' => '+44 113 496 0345',
                'email' => null,
            ],
            [
                'contactable_type' => $morphType,
                'contactable_id' => $users['data@example.com']->id,
                'phone' => '+44 28 9024 6200',
                'email' => 'conor.obryan@example.co.uk',
            ],
        ];
    }
}
