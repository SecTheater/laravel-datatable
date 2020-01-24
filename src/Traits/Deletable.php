<?php

namespace Laravel\DataTables\Traits;

trait Deletable
{
    /**
     * @var mixed
     */
    public $allowDeleting = false;

    /**
     * @param Model $result
     * @param Request $request
     * @return bool|null
     */
    public function destroy($ids):  ? bool
    {
        $ids = (array) $ids;
        if (!$this->allowDeleting) {
            return null;
        }

        return $this->builder()->whereIn(
            $this->builder()->getModel()->getQualifiedKeyName(), $ids
        )->delete();

    }
}
