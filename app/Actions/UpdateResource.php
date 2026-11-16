<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class UpdateResource
{
    /**
     * Apply data to the model then run post-update side effects, returning the refreshed model.
     *
     * @template TModel of Model
     *
     * @param  TModel  $model
     * @param  array<string, mixed>  $data
     * @param  callable(TModel): void  $afterUpdate
     * @return TModel
     *
     * @throws ModelNotFoundException
     */
    public function handle(Model $model, array $data, callable $afterUpdate): Model
    {
        return DB::transaction(function () use ($model, $data, $afterUpdate): Model {
            $model->update($data);
            $afterUpdate($model);

            return $model->refresh();
        });
    }
}
