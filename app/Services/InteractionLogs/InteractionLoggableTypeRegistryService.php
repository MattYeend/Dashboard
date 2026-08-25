<?php

namespace App\Services\InteractionLogs;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class InteractionLoggableTypeRegistryService
{
    /**
     * Allow-listed interactable types: short UI key => [FQCN, label field, label].
     *
     * @var array<string, array{class: class-string, label_field: string, label: string}>
     */
    private array $registry = [
        'company' => [
            'class' => Company::class,
            'label_field' => 'name', '
            label' => 'Company',
        ],
        'contact' => [
            'class' => Contact::class,
            'label_field' => 'name',
            'label' => 'Contact',
        ],
        'deal' => [
            'class' => Deal::class,
            'label_field' => 'title',
            'label' => 'Deal',
        ],
    ];

    /**
     * Get the full registry.
     *
     * @return array<string, array{class: class-string, label_field: string, label: string}>
     */
    public function all(): array
    {
        return $this->registry;
    }

    /**
     * Get the list of types for a UI <select>.
     *
     * @return Collection<int, array{key: string, label: string}>
     */
    public function types(): Collection
    {
        return collect($this->registry)->map(fn (array $entry, string $key) => [
            'key' => $key,
            'label' => $entry['label'],
        ])->values();
    }

    /**
     * Get dependent dropdown options for a given type key.
     *
     * @return Collection<int, array{id: int, name: string}>
     */
    public function optionsFor(string $type): Collection
    {
        if (! array_key_exists($type, $this->registry)) {
            throw new InvalidArgumentException("Unrecognised interactable type [{$type}].");
        }

        $entry = $this->registry[$type];
        $labelField = $entry['label_field'];

        return $entry['class']::query()
            ->orderBy($labelField)
            ->get(['id', $labelField])
            ->map(fn ($model) => [
                'id' => $model->id,
                'name' => $model->{$labelField},
            ]);
    }

    /**
     * Get the short UI key for a given model FQCN.
     */
    public function keyForModel(?string $fqcn): ?string
    {
        foreach ($this->registry as $key => $entry) {
            if ($entry['class'] === $fqcn) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Get the human-readable label for a given model FQCN.
     */
    public function labelForModel(?string $fqcn): ?string
    {
        $key = $this->keyForModel($fqcn);

        return $key !== null ? $this->registry[$key]['label'] : null;
    }

    /**
     * Resolve a short UI key submitted by the form into the FQCN to persist.
     *
     * @return class-string
     */
    public function modelClassForKey(string $key): string
    {
        if (! array_key_exists($key, $this->registry)) {
            throw new InvalidArgumentException("Unrecognised interactable type [{$key}].");
        }

        return $this->registry[$key]['class'];
    }
}
