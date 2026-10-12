<?php

declare(strict_types=1);

return [
    /** ISO 4217 codes selectable across Orders, Invoices, and Deals. */
    'allowed' => [
        'GBP',
        'USD',
        'EUR',
        'AUD',
        'CAD',
    ],

    'default' => 'GBP',
];
