<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LogicException;

class RestoreResource
{
    /**
     * Restore the model then run post-restoration side effects, returning the refreshed model.
     *
     * @template TModel of Model
     *
     * @param  TModel  $model
     * @param  callable(TModel): void  $afterRestore
     * @return TModel
     *
     * @throws LogicException
     */
    public function handle(Model $model, callable $afterRestore): Model
    {
        if (! method_exists($model, 'restore')) {
            throw new LogicException($model::class . ' does not support soft deletes.');
        }

        return DB::transaction(function () use ($model, $afterRestore) {
            $model->restore();
            $afterRestore($model);

            return $model->refresh();
        });
    }
}
