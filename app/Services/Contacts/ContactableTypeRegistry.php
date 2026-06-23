<?php

namespace App\Services\Contacts;

use App\Models\User;
// use App\Models\Company;
use App\Models\Task;

class ContactableTypeRegistry
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
        $config = $this->all()[$type] ?? null;

        if (!$config) {
            return [];
        }

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
}
