<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugService
{
    /**
     * Generate a unique slug for the given model.
     *
     * @param  class-string<Model>  $modelClass
     */
    public function generateUnique(
        string $modelClass,
        string $name,
        ?int $ignoreId = null,
        string $column = 'slug'
    ): string {
        $base = Str::slug($name);
        $slug = $base !== '' ? $base : Str::slug(class_basename($modelClass));
        $suffix = 1;

        while ($this->exists($modelClass, $column, $slug, $ignoreId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * Determine whether a slug already exists for the given model.
     */
    protected function exists(string $modelClass, string $column, string $slug, ?int $ignoreId): bool
    {
        return $modelClass::withTrashed()
            ->where($column, $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
