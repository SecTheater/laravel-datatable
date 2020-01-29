<?php

namespace Laravel\DataTables\Traits;

use Arr;
use Illuminate\Database\Eloquent\Model;

trait Updatable
{
    /**
     * Allow Entity Updating.
     *
     * @var bool
     */
    public $allowUpdating = false;

    /**
     * @param Model $model
     * @param array $data
     * @return Model
     */
    public function update(Model $model, array $data = []): ?Model
    {
        if (! $this->allowUpdating) {
            return null;
        }

        return tap($model, function ($model) use ($data) {
            $model->update(Arr::only($data, $this->getUpdatableColumns()));
        });
    }
}
