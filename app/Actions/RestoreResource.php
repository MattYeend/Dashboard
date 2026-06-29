<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RestoreResource
{
    public function handle(Model $model, callable $afterRestore): Model
    {
        return DB::transaction(function () use ($model, $afterRestore) {
            $model->restore();
            $afterRestore($model);

            return $model->fresh();
        });
    }
}
