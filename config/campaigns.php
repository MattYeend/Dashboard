<?php

declare(strict_types=1);

return [
    'emails_per_minute' => (int) env('CAMPAIGN_EMAILS_PER_MINUTE', 60),
    'from_address' => env('CAMPAIGN_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
    'postal_address' => env('CAMPAIGN_POSTAL_ADDRESS', ''),
    'track_opens' => (bool) env('CAMPAIGNS_TRACK_OPENS', false),
];
