<?php

namespace App\Services\Attachments;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Order;

class AttachableTypeRegistryService
{
    /**
     * Allow-list of attachable types. Keys are short, UI-facing identifiers.
     * 'model' is the fully-qualified class name actually stored in the
     * attachments.attachable_type column (no morph map aliasing is used).
     *
     * This is the single gate that decides which models can have files
     * attached — never accept a raw class name from the client.
     */
    public function all(): array
    {
        return [
            'company' => [
                'label' => 'Company',
                'model' => Company::class,
                'label_field' => 'name',
            ],
            'contact' => [
                'label' => 'Contact',
                'model' => Contact::class,
                'label_field' => 'title',
            ],
            'deal' => [
                'label' => 'Deal',
                'model' => Deal::class,
                'label_field' => 'name',
            ],
            'order' => [
                'label' => 'Order',
                'model' => Order::class,
                'label_field' => 'reference',
            ],
        ];
    }

    /**
     * Resolve the FQCN that should be persisted to
     * attachments.attachable_type, given a short key submitted by the form.
     * Returns null if the key isn't in the allow-list.
     */
    public function modelClassForKey(string $key): ?string
    {
        return $this->all()[$key]['model'] ?? null;
    }

    /**
     * Resolve a stored FQCN back to its short UI key.
     */
    public function keyForModel(?string $modelClass): string
    {
        if (! $modelClass) {
            return '';
        }

        foreach ($this->all() as $key => $config) {
            if ($config['model'] === $modelClass) {
                return $key;
            }
        }

        return '';
    }

    /**
     * Resolve the human-readable label for a stored FQCN.
     */
    public function labelForModel(?string $modelClass): ?string
    {
        foreach ($this->all() as $config) {
            if ($config['model'] === $modelClass) {
                return $config['label'];
            }
        }

        return null;
    }
}
