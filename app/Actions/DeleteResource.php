<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeleteResource
{
    /**
     * Run pre-deletion side effects then soft delete the model.
     *
     * @param  callable(Model): void  $beforeDelete
     */
    public function handle(Model $model, callable $beforeDelete): bool
    {
        return DB::transaction(function () use ($model, $beforeDelete) {
            $beforeDelete($model);

            return $model->delete();
        });
    }

    /**
     * Run pre-deletion side effects then permanently delete the model.
     *
     * @param  callable(Model): void  $beforeDelete
     */
    public function forceHandle(Model $model, callable $beforeDelete): bool
    {
        return DB::transaction(function () use ($model, $beforeDelete) {
            $beforeDelete($model);

            return $model->forceDelete();
        });
    }
}
