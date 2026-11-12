<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Models\Company;
use App\Models\Organisation;
use App\Models\User;

class QuotableTypeRegistryService
{
    /**
     * Allow-list of quotable types. Keys are short, UI-facing identifiers.
     * 'model' is the fully-qualified class name stored in quotes.quotable_type.
     *
     * @return array<string, array{label: string, model: class-string, label_field: string}>
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
     * Short keys and labels for the customer type select.
     *
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
     * Options for the customer select, for a given short type key.
     *
     * @return array<int, array{value: int, label: string}>
     */
    public function optionsFor(string $type): array
    {
        $config = $this->all()[$type] ?? null;

        if ($config === null) {
            return [];
        }

        // Only ever use pre-registered model classes, never user-supplied class names.
        $model = $config['model'];
        $field = $config['label_field'];

        $query = $model === User::class
            ? Organisation::current()?->activeUsers() ?? User::query()->whereRaw('1 = 0')
            : $model::query();

        return $query
            ->orderBy($model === User::class ? 'users.'.$field : $field)
            ->get($model === User::class ? ['users.id', 'users.'.$field] : ['id', $field])
            ->map(fn ($item) => ['value' => $item->id, 'label' => $item->{$field}])
            ->all();
    }

    /**
     * Resolve a stored FQCN to its short UI key, or an empty string.
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
     * Resolve the FQCN to persist for a short key, or null if not allowed.
     */
    public function modelClassForKey(string $key): ?string
    {
        return $this->all()[$key]['model'] ?? null;
    }
}
