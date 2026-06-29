<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateResource
{
    /**
     * Run the creator callable inside a transaction and return a fresh model instance.
     *
     * @param  array<string, mixed>  $data
     * @param  callable(array<string, mixed>): Model  $creator
     */
    public function handle(array $data, callable $creator): Model
    {
        return DB::transaction(function () use ($data, $creator) {
            $model = $creator($data);

            return $model->fresh();
        });
    }
}
