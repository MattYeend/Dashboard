<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateResource
{
    public function handle(array $data, callable $creator): Model
    {
        return DB::transaction(function () use ($data, $creator) {
            $model = $creator($data);

            return $model->fresh();
        });
    }
}
