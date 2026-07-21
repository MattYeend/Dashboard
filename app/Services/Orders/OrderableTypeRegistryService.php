<?php

namespace App\Services\Orders;

use App\Models\Company;
use App\Models\User;

class OrderableTypeRegistryService
{
    /**
     * Allow-list of orderable types. Keys are short, UI-facing identifiers.
     * 'model' is the fully-qualified class name actually stored in the
     * orders.orderable_type column (no morph map aliasing is used).
     */
    public function all(): array
    {
        return [
            'user' => [
                'label' => 'User',
                'model' => User::class,
                'label_field' => 'name',
            ],
            'company' => [
                'label' => 'Company',
                'model' => Company::class,
                'label_field' => 'name',
            ],
        ];
    }

    /**
     * Short keys + labels for populating the "order type" <select>.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function types(): array
    {
        return collect($this->all())
            ->map(fn ($config, $key) => [
                'value' => $key,
                'label' => $config['label'],
            ])
            ->values()
            ->all();
    }

    /**
     * Options for the "order owner" <select>, keyed by short type.
     *
     * @return array<int, array{value: int, label: string}>
     */
    public function optionsFor(string $type): array
    {
        $allowed = $this->all();

        // Normalise either a short key or a stored FQCN back to a short key
        $resolvedType = $this->resolveTypeKey($type, $allowed);

        $config = $allowed[$resolvedType] ?? null;

        if ($config === null) {
            return [];
        }

        // Only ever use pre-registered model classes - never user-supplied class names
        $model = $config['model'];
        $field = $config['label_field'];

        return $model::query()
            ->orderBy($field)
            ->get(['id', $field])
            ->map(fn ($item) => [
                'value' => $item->id,
                'label' => $item->{$field},
            ])
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
     * orders.orderable_type, given a short key submitted by the form
     * (e.g. "user" -> "App\Models\User"). Returns null if the key isn't
     * in the allow-list.
     */
    public function modelClassForKey(string $key): ?string
    {
        return $this->all()[$key]['model'] ?? null;
    }

    /**
     * Resolve a type string to a registered short key.
     * Accepts either the short key (e.g. "user") or the fully-qualified
     * class name (e.g. "App\Models\User"), but only returns keys that
     * exist in the allow-list.
     */
    private function resolveTypeKey(string $type, array $allowed): string
    {
        if (isset($allowed[$type])) {
            return $type;
        }

        foreach ($allowed as $key => $config) {
            if ($config['model'] === $type) {
                return $key;
            }
        }

        return '';
    }
}
