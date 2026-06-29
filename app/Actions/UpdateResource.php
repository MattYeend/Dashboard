<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UpdateResource
{
    /**
     * Apply data to the model then run post-update side effects, returning a fresh instance.
     *
     * @param  array<string, mixed>   $data
     * @param  callable(Model): void  $afterUpdate
     */
    public function handle(Model $model, array $data, callable $afterUpdate): Model
    {
        return DB::transaction(function () use ($model, $data, $afterUpdate) {
            $model->update($data);
            $afterUpdate($model);

            return $model->fresh();
        });
    }
}
