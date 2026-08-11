<?php

namespace App\Traits;

use App\Models\Label;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 * @method MorphToMany morphToMany(string $related, string $name, ?string $table = null, ?string $foreignPivotKey = null, ?string $relatedPivotKey = null, ?string $parentKey = null, ?string $relatedKey = null, ?string $relation = null, bool $inverse = false)
 */
trait Labelable
{
    /**
     * @return MorphToMany<Label, $this>
     */
    public function labels(): MorphToMany
    {
        return $this->morphToMany(Label::class, 'labelable')->withTimestamps();
    }
}
