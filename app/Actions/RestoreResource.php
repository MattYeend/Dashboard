<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RestoreResource
{
    /**
     * Restore the model then run post-restoration side effects, returning a fresh instance.
     *
     * @param  callable(Model): void  $afterRestore
     */
    public function handle(Model $model, callable $afterRestore): Model
    {
        return DB::transaction(function () use ($model, $afterRestore) {
            $model->restore();
            $afterRestore($model);

            return $model->fresh();
        });
    }
}
