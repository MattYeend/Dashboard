<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CreateResource
{
    /**
     * Run the creator callable inside a transaction and return the refreshed model.
     *
     * @template TModel of Model
     *
     * @param  array<string, mixed>  $data
     * @param  callable(array<string, mixed>): TModel  $creator
     * @return TModel
     *
     * @throws ModelNotFoundException
     */
    public function handle(array $data, callable $creator): Model
    {
        return DB::transaction(function () use ($data, $creator): Model {
            $model = $creator($data);

            return $model->refresh();
        });
    }
}
