<?php

namespace Laravel\DataTables\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Laravel\DataTables\Services\BaseDataTableService;

abstract class BaseResource extends JsonResource
{
    /**
     * @param BaseDatatableService $dataTable
     */
    abstract public function dataTable(): BaseDataTableService;

    /**
     * @param $resource
     */
    public function rejectNullValues($resource)
    {
        return array_filter($resource, function ($value) {
            return ! is_null($value) || ! empty($value);
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
        return $this->rejectNullValues([
            'id' => $this->id,
            'created_at_human' => optional($this->created_at)->diffForHumans(),
            'updated_at_human' => optional($this->updated_at)->diffForHumans(),
        ] + Arr::only(
            $this->resource->toArray(),
            $this->dataTable()->getDisplayableColumns()
        ));
    }
}
