<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeleteResource
{
    public function handle(Model $model, callable $beforeDelete): bool
    {
        return DB::transaction(function () use ($model, $beforeDelete) {
            $beforeDelete($model);

            return $model->delete();
        });
    }

    public function forceHandle(Model $model, callable $beforeDelete): bool
    {
        return DB::transaction(function () use ($model, $beforeDelete) {
            $beforeDelete($model);

            return $model->forceDelete();
        });
    }
}
