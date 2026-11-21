<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;

/**
 * Seeds real email templates for the default organisation. No Faker data.
 * Safe to run repeatedly: rows are matched on 'name'.
 */
class EmailTemplateSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    public function run(): void
    {
        $organisation = $this->defaultOrganisation();

        $rows = [
            [
                'name' => 'Welcome',
                'subject' => 'Welcome to {{ company }}',
                'preheader' => 'A quick hello and what to expect',
                'is_active' => true,
                'body_html' => '<p>Hello {{ first_name }},</p><p>Thank you for getting in touch. We are looking forward to working with you.</p><p><a href="{{ unsubscribe_url }}">Unsubscribe</a></p>',
                'body_text' => 'Hello {{ first_name }},

Thank you for getting in touch. We are looking forward to working with you.

Unsubscribe: {{ unsubscribe_url }}',
            ],
            [
                'name' => 'Monthly Newsletter',
                'subject' => 'Your monthly update',
                'preheader' => 'News, tips and what is coming next',
                'is_active' => true,
                'body_html' => '<p>Hello {{ first_name }},</p><p>Here is what has been happening this month.</p><p><a href="{{ unsubscribe_url }}">Unsubscribe</a></p>',
                'body_text' => 'Hello {{ first_name }},

Here is what has been happening this month.

Unsubscribe: {{ unsubscribe_url }}',
            ],
            [
                'name' => 'Service Announcement',
                'subject' => 'An update to our service',
                'preheader' => 'Please read: a change that affects you',
                'is_active' => true,
                'body_html' => '<p>Hello {{ first_name }},</p><p>We are making a change to our service. Details are below.</p><p><a href="{{ unsubscribe_url }}">Unsubscribe</a></p>',
                'body_text' => 'Hello {{ first_name }},

We are making a change to our service. Details are below.

Unsubscribe: {{ unsubscribe_url }}',
            ],
        ];

        foreach ($rows as $row) {
            EmailTemplate::firstOrCreate(
                [
                    'name' => $row['name'],
                    'organisation_id' => $organisation->id,
                ],
                $row,
            );
        }
    }
}
