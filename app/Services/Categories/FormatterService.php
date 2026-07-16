<?php

namespace App\Services\Categories;

use App\Models\Category;

class FormatterService
{
    /**
     * Format a single category with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Category $category): array
    {
        return [
            'id' => $category->id,
            'parent_id' => $category->parent_id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'meta' => $category->meta,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
            'deleted_at' => $category->deleted_at,
            'restored_at' => $category->restored_at,
            'parent' => $category->parent ? ['id' => $category->parent->id, 'name' => $category->parent->name] : null,
            'creator' => $category->creator ? ['id' => $category->creator->id, 'name' => $category->creator->name] : null,
            'updater' => $category->updater ? ['id' => $category->updater->id, 'name' => $category->updater->name] : null,
            'deleter' => $category->deleter ? ['id' => $category->deleter->id, 'name' => $category->deleter->name] : null,
            'restorer' => $category->restorer ? ['id' => $category->restorer->id, 'name' => $category->restorer->name] : null,
        ];
    }
}
