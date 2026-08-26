<?php

namespace App\Services\Activities;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Order;

class ActivityableTypeRegistryService
{
    /**
     * Allow-list of activityable types. Keys are short, UI-facing
     * identifiers. 'model' is the FQCN actually stored in
     * activities.activityable_type (no morph map aliasing).
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
     * @return array<int, array{value: string, label: string}>
     */
    public function types(): array
    {
        return collect($this->all())
            ->map(fn ($config, $key) => ['value' => $key, 'label' => $config['label']])
            ->values()
            ->all();
    }

    /**
     * Resolve a stored FQCN (e.g. "App\Models\User") to its short UI key
     * (e.g. "user"). Used when hydrating the edit form.
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
        if (! $modelClass) {
            return null;
        }

        foreach ($this->all() as $config) {
            if ($config['model'] === $modelClass) {
                return $config['label'];
            }
        }

        return null;
    }

    /**
     * Resolve the FQCN that should actually be persisted to
     * activities.activityable_type, given a short key submitted by the
     * form (e.g. "company" -> "App\Models\Company"). Returns null if the
     * key isn't in the allow-list — never trust a raw class name from
     * the client.
     */
    public function modelClassForKey(string $key): ?string
    {
        return $this->all()[$key]['model'] ?? null;
    }
}
