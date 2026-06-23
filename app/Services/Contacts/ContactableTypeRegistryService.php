<?php

namespace App\Services\Contacts;

use App\Models\Task;
// use App\Models\Company;
use App\Models\User;

class ContactableTypeRegistryService
{
    public function all(): array
    {
        return [
            'user' => [
                'label' => 'User',
                'model' => User::class,
                'label_field' => 'name',
            ],
            // 'company' => [
            //     'label' => 'Company',
            //     'model' => Company::class,
            //     'label_field' => 'name',
            // ],
            'task' => [
                'label' => 'Task',
                'model' => Task::class,
                'label_field' => 'title',
            ],
        ];
    }

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

    public function optionsFor(string $type): array
    {
        $allowed = $this->all();

        // Normalise fully-qualified class names back to short keys
        $resolvedType = $this->resolveTypeKey($type, $allowed);

        $config = $allowed[$resolvedType] ?? null;

        if ($config === null) {
            return [];
        }

        // Only ever use pre-registered model classes — never user-supplied class names
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

    public function keyForModel(string $modelClass): string
    {
        foreach ($this->all() as $key => $config) {
            if ($config['model'] === $modelClass) {
                return $key;
            }
        }

        return $modelClass;
    }

    /**
     * Resolve the human-readable label for a fully-qualified model class.
     */
    public function labelForModel(string $modelClass): ?string
    {
        foreach ($this->all() as $config) {
            if ($config['model'] === $modelClass) {
                return $config['label'];
            }
        }

        return null;
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

        return $type;
    }
}
