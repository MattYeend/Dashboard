<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasLikes
{
    /**
     * Get the likes belonging to this model.
     */
    public function likes(): MorphMany;
}
