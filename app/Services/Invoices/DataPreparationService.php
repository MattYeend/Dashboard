<?php

namespace App\Services\Invoices;

class DataPreparationService
{
    /**
     * Prepare invoice data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'invoice_number' => $data['invoice_number'],
            'company_id' => $data['company_id'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'status_id' => $data['status_id'] ?? null,
            'issue_date' => $data['issue_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'subtotal' => $data['subtotal'] ?? 0,
            'tax_total' => $data['tax_total'] ?? 0,
            'total' => $data['total'] ?? 0,
            'currency' => $data['currency'] ?? 'GBP',
            'notes' => $data['notes'] ?? null,
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare invoice data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $allowed = [
            'invoice_number',
            'company_id',
            'order_id',
            'status_id',
            'issue_date',
            'due_date',
            'subtotal',
            'tax_total',
            'total',
            'currency',
            'notes',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        return $payload;
    }

    /**
     * Prepare the invoice's contact data for creation.
     *
     * The contact is stored via the polymorphic `contacts` table
     * (contactable_type = Invoice), not via a foreign key on invoices,
     * so it is intentionally kept separate from the invoice payload above.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function prepareForContactCreation(array $data): ?array
    {
        if (! isset($data['contact']) || ! is_array($data['contact'])) {
            return null;
        }

        $contact = $data['contact'];

        return [
            'phone' => $contact['phone'] ?? null,
            'email' => $contact['email'] ?? null,
            'meta' => $contact['meta'] ?? null,
        ];
    }

    /**
     * Prepare address data for the invoice contact's address.
     *
     * Maps the legacy flat contact fields (address/city/postal_code/
     * country) onto the addresses table's actual column names. Returns
     * null when none of those fields were supplied, since an empty
     * address record should never be created.
     *
     * city and country are non-nullable columns on Address, so a
     * 'Not provided' fallback is used when a value is missing -
     * matching the fallback already used by the historical
     * migrate_contact_addresses_to_addresses_table migration.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function prepareAddressForContactCreation(array $data): ?array
    {
        if (! isset($data['contact']) || ! is_array($data['contact'])) {
            return null;
        }

        $contact = $data['contact'];

        if (! array_key_exists('address', $contact)
            && ! array_key_exists('city', $contact)
            && ! array_key_exists('postal_code', $contact)
            && ! array_key_exists('country', $contact)) {
            return null;
        }

        return [
            'address_line_one' => $contact['address'] ?? 'Not provided',
            'city' => $contact['city'] ?? 'Not provided',
            'postcode' => $contact['postal_code'] ?? null,
            'country' => $contact['country'] ?? 'Not provided',
            'is_primary' => true,
        ];
    }

    /**
     * Prepare the invoice's contact data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function prepareUpdate(array $data): ?array
    {
        if (! isset($data['contact']) || ! is_array($data['contact'])) {
            return null;
        }

        $allowed = [
            'phone',
            'email',
            'meta',
        ];
        $contact = $data['contact'];
        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $contact)) {
                $payload[$field] = $contact[$field];
            }
        }

        return $payload;
    }
}
