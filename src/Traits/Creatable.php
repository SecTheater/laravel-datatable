<?php

namespace Laravel\DataTables\Traits;

use Arr;
use Illuminate\Database\Eloquent\Model;
trait Creatable
{
    /**
     * Allow Entity Creation
     *
     * @var bool
     */
    public $allowCreation = false;

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data): ?Model
    {
        if (!$this->allowCreation) {
            return null;
        }

        return $this->builder()->create(
            Arr::only(
                $data,
                $this->getUpdatableColumns()
            )
        );
    }
}
