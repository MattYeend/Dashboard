<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Ticket::exists()) {
            $this->command->info('Tickets already seeded, skipping...');

            return;
        }

        $statuses = TicketStatus::all()->keyBy('title');
        $priorities = TicketPriority::all()->keyBy('title');
        $users = User::orderBy('id')->get();

        if ($statuses->isEmpty() || $priorities->isEmpty()) {
            $this->command->warn('No ticket statuses or priorities found, skipping ticket seeding...');

            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found, skipping ticket seeding...');

            return;
        }

        $creator = $users->first();

        foreach ($this->getTickets($statuses, $priorities, $users) as $ticket) {
            Ticket::create([
                ...$ticket,
                'created_by' => $creator->id,
            ]);
        }
    }

    /**
     * Get the predefined ticket records to seed.
     *
     * @param  Collection<string, TicketStatus>  $statuses
     * @param  Collection<string, TicketPriority>  $priorities
     * @param  Collection<int, User>  $users
     * @return array<int, array<string, string|int|null>>
     */
    private function getTickets($statuses, $priorities, $users): array
    {
        return [
            [
                'title' => 'Unable to reset password',
                'description' => 'Customer reports the password reset email never arrives, even after several attempts.',
                'ticket_status_id' => $statuses->get('Open')?->id,
                'ticket_priority_id' => $priorities->get('High')?->id,
                'assigned_to' => $users->first()?->id,
                'due_date' => now()->addDays(2)->toDateString(),
                'resolved_at' => null,
                'meta' => null,
            ],
            [
                'title' => 'Invoice PDF shows incorrect VAT rate',
                'description' => 'Generated invoices are applying the old VAT rate rather than the current one.',
                'ticket_status_id' => $statuses->get('In Progress')?->id,
                'ticket_priority_id' => $priorities->get('Urgent')?->id,
                'assigned_to' => $users->get(1)?->id ?? $users->first()?->id,
                'due_date' => now()->addDay()->toDateString(),
                'resolved_at' => null,
                'meta' => null,
            ],
            [
                'title' => 'Dashboard widgets loading slowly',
                'description' => 'Several customers have reported the dashboard taking over ten seconds to load during peak hours.',
                'ticket_status_id' => $statuses->get('In Progress')?->id,
                'ticket_priority_id' => $priorities->get('Medium')?->id,
                'assigned_to' => $users->get(2)?->id ?? $users->first()?->id,
                'due_date' => now()->addWeek()->toDateString(),
                'resolved_at' => null,
                'meta' => null,
            ],
            [
                'title' => 'Add bulk export to CSV',
                'description' => 'Customer has requested the ability to export their records to CSV in bulk from the listing page.',
                'ticket_status_id' => $statuses->get('Open')?->id,
                'ticket_priority_id' => $priorities->get('Low')?->id,
                'assigned_to' => null,
                'due_date' => now()->addWeeks(2)->toDateString(),
                'resolved_at' => null,
                'meta' => null,
            ],
            [
                'title' => 'Login page returns 500 on mobile Safari',
                'description' => 'The login form throws a server error specifically on iOS Safari, other browsers are unaffected.',
                'ticket_status_id' => $statuses->get('Resolved')?->id,
                'ticket_priority_id' => $priorities->get('High')?->id,
                'assigned_to' => $users->first()?->id,
                'due_date' => now()->subDays(3)->toDateString(),
                'resolved_at' => now()->subDay(),
                'meta' => null,
            ],
            [
                'title' => 'Update terms of service link in footer',
                'description' => 'The footer links to an outdated terms of service page, it needs pointing at the new URL.',
                'ticket_status_id' => $statuses->get('Closed')?->id,
                'ticket_priority_id' => $priorities->get('Low')?->id,
                'assigned_to' => $users->get(1)?->id ?? $users->first()?->id,
                'due_date' => now()->subWeek()->toDateString(),
                'resolved_at' => now()->subDays(5),
                'meta' => null,
            ],
            [
                'title' => 'Duplicate email notifications on task assignment',
                'description' => 'Users assigned to a task are receiving the same notification email twice.',
                'ticket_status_id' => $statuses->get('On Hold')?->id,
                'ticket_priority_id' => $priorities->get('Medium')?->id,
                'assigned_to' => $users->get(2)?->id ?? $users->first()?->id,
                'due_date' => null,
                'resolved_at' => null,
                'meta' => null,
            ],
            [
                'title' => 'Improve search relevance on customer records',
                'description' => 'Search results do not consistently prioritise exact matches over partial matches.',
                'ticket_status_id' => $statuses->get('Open')?->id,
                'ticket_priority_id' => $priorities->get('Medium')?->id,
                'assigned_to' => null,
                'due_date' => now()->addWeeks(3)->toDateString(),
                'resolved_at' => null,
                'meta' => null,
            ],
        ];
    }
}
