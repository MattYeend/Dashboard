<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UpdateResource
{
    public function handle(Model $model, array $data, callable $afterUpdate): Model
    {
        return DB::transaction(function () use ($model, $data, $afterUpdate) {
            $model->update($data);
            $afterUpdate($model);

            return $model->fresh();
        });
    }
}
