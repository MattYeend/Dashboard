<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugService
{
    /**
     * Generate a unique slug for the given model.
     *
     * Pass $scope (column => value) to make the slug unique within a parent,
     * for example ['course_id' => 5] for lessons.
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<string, int|string>  $scope
     */
    public function generateUnique(
        string $modelClass,
        string $name,
        ?int $ignoreId = null,
        string $column = 'slug',
        array $scope = []
    ): string {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : Str::slug(class_basename($modelClass));
        $slug = $base;
        $suffix = 1;

        while ($this->exists($modelClass, $column, $slug, $ignoreId, $scope)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * Determine whether a slug already exists for the given model.
     *
     * @param  array<string, int|string>  $scope
     */
    protected function exists(
        string $modelClass,
        string $column,
        string $slug,
        ?int $ignoreId,
        array $scope = []
    ): bool {
        return $modelClass::withTrashed()
            ->where($column, $slug)
            ->where($scope)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
