<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CampaignStatus;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Database\Seeders\Concerns\SeedsSystemStatuses;
use Illuminate\Database\Seeder;

class CampaignStatusSeeder extends Seeder
{
    use ResolvesDefaultOrganisation,
        SeedsSystemStatuses;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organisation = $this->defaultOrganisation();

        $statuses = [
            [
                'system_key' => CampaignStatus::KEY_DRAFT,
                'title' => 'Draft',
                'description' => 'Campaign is being prepared and has not yet been scheduled.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
            ],
            [
                'system_key' => CampaignStatus::KEY_SCHEDULED,
                'title' => 'Scheduled',
                'description' => 'Campaign is ready and will be sent at the chosen date and time.',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
            ],
            [
                'system_key' => CampaignStatus::KEY_SENDING,
                'title' => 'Sending',
                'description' => 'Campaign is currently being delivered to its recipients.',
                'background_colour' => '#d6bcfa',
                'text_colour' => '#553c9a',
            ],
            [
                'system_key' => CampaignStatus::KEY_SENT,
                'title' => 'Sent',
                'description' => 'Campaign has been delivered to all of its recipients.',
                'background_colour' => '#c6f6d5',
                'text_colour' => '#22543d',
            ],
            [
                'system_key' => CampaignStatus::KEY_PAUSED,
                'title' => 'Paused',
                'description' => 'Campaign delivery has been paused and can be resumed.',
                'background_colour' => '#fefcbf',
                'text_colour' => '#744210',
            ],
            [
                'system_key' => CampaignStatus::KEY_CANCELLED,
                'title' => 'Cancelled',
                'description' => 'Campaign has been cancelled and will not be sent.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#718096',
            ],
            [
                'system_key' => CampaignStatus::KEY_FAILED,
                'title' => 'Failed',
                'description' => 'Campaign could not be delivered due to a sending or processing failure.',
                'background_colour' => '#feb2b2',
                'text_colour' => '#822727',
            ],
        ];

        $this->seedSystemStatuses(CampaignStatus::class, (int) $organisation->id, $statuses);
    }
}
