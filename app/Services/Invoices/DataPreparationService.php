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
    public function prepareForCreation(array $data, int $createdBy): array
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
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare invoice data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, int $updatedBy): array
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

        $payload['updated_by'] = $updatedBy;

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
    public function prepareContactForCreation(array $data): ?array
    {
        if (! isset($data['contact']) || ! is_array($data['contact'])) {
            return null;
        }

        $contact = $data['contact'];

        return [
            'phone' => $contact['phone'] ?? null,
            'email' => $contact['email'] ?? null,
            'address' => $contact['address'] ?? null,
            'city' => $contact['city'] ?? null,
            'postal_code' => $contact['postal_code'] ?? null,
            'country' => $contact['country'] ?? null,
            'meta' => $contact['meta'] ?? null,
        ];
    }

    /**
     * Prepare the invoice's contact data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function prepareContactForUpdate(array $data): ?array
    {
        if (! isset($data['contact']) || ! is_array($data['contact'])) {
            return null;
        }

        $allowed = ['phone', 'email', 'address', 'city', 'postal_code', 'country', 'meta'];
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
