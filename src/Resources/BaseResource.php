<?php

namespace Laravel\DataTables\Resources;

use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\DataTables\Services\BaseDataTableService;

abstract class BaseResource extends JsonResource
{
    /**
     * @param BaseDatatableService $dataTable
     */
    abstract public function dataTable(): BaseDataTableService;

    /**
     * @param $collection
     */
    public function rejectNullValues($collection)
    {
        return array_filter($collection, function ($resource) {
            return !is_null($resource) || !empty($resource);
        });
    }

    /**
     * @param $request
     */
    /**
     * @param $request
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'created_at_human' => optional($this->created_at)->diffForHumans(),
            'updated_at_human' => optional($this->updated_at)->diffForHumans(),
        ] + Arr::only(
            $this->resource->toArray(), $this->dataTable()->getDisplayableColumns()
        );
    }
}
