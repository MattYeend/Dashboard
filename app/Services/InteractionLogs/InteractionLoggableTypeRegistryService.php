<?php

namespace App\Services\InteractionLogs;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;

class InteractionLoggableTypeRegistryService
{
    /**
     * Allow-list of interactable types. Keys are short, UI-facing
     * identifiers. 'model' is the FQCN actually stored in
     * interaction_logs.interactable_type (no morph map aliasing).
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
                'label_field' => 'name',
            ],
            'deal' => [
                'label' => 'Deal',
                'model' => Deal::class,
                'label_field' => 'title',
            ],
        ];
    }

    /**
     * Short keys + labels for populating the "interactable type" <select>.
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
     * Options for the "interactable owner" <select>, keyed by short type.
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
     * Resolve a stored FQCN (e.g. "App\Models\Company") to its short UI key
     * (e.g. "company"). Used when hydrating the edit form.
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
     * interaction_logs.interactable_type, given a short key submitted by
     * the form (e.g. "company" -> "App\Models\Company"). Returns null if
     * the key isn't in the allow-list — never trust a raw class name
     * from the client.
     */
    public function modelClassForKey(string $key): ?string
    {
        return $this->all()[$key]['model'] ?? null;
    }

    /**
     * Resolve a type string to a registered short key.
     * Accepts either the short key (e.g. "company") or the fully
     * qualified class name (e.g. "App\Models\Company"), but only
     * returns keys that exist in the allow-list.
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
