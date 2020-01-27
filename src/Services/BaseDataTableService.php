<?php
namespace Laravel\DataTables\Services;

use Arr;
use Schema;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Laravel\DataTables\Contracts\Displayable;
use Laravel\DataTables\Exceptions\InvalidColumnSearchException;
use Laravel\DataTables\Exceptions\EloquentBuilderWasSetToNullException;

abstract class BaseDataTableService implements Displayable
{
    use Macroable;

    /**
     * Load the relationships associated with the collection that will be returned.
     *
     * @var array
     */
    public $relations;

    /**
     * Get/Set the eloquent builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function builder(): Builder
    {
        /**
         * @var mixed
         */
        static $builder = null;

        if (!is_null($builder)) {
            return $builder = $this->query()->newQuery();
        }
        $builder = $this->query();

        return $builder;
    }

    /**
     * @param $columns
     */
    public function getColumnsWithoutPrimaryKey($columns)
    {
        $primaryKey = $this->getModel()->getKeyName();

        return array_filter($columns, function ($column) use ($primaryKey) {
            return $primaryKey !== $column;
        });
    }

    /**
     * Get Custom Column Names.
     *
     * @return array
     */
    public function getCustomColumnNames(): array
    {
        if (method_exists($model = $this->getModel(), 'getCustomColumnNames')) {
            return $model->getCustomColumnNames();
        }

        return [];
    }

    /**
     * Get displayable columns.
     *
     * @return array
     */
    public function getDisplayableColumns(): array
    {

        if (method_exists($model = $this->getModel(), 'getDisplayableColumns')) {
            return $model->getDisplayableColumns();
        }

        return array_diff(
            $this->getDatabaseColumnNames(),
            $this->getModel()->getHidden()
        );
    }

    /**
     * @return mixed
     */
    public function getModel(): Model
    {
        /**
         * @var mixed
         */
        static $model = null;
        if (!is_null($model)) {
            return $model;
        }

        return $model = $this->builder()->getModel();
    }

    /**
     * Fetch records from the database.
     *
     * @param  Request    $request
     * @param  callable   $callback
     * @return Collection
     */
    public function getRecords(Request $request = null, callable $callback = null): Collection
    {
        $builder = $this->builder();

        // we will check if the request has a query string for search. the query string for searching must contain column, operator which identified at resolveQueryParts method in form of the keys of the array. and the value that user is trying to search for
        // example: http://localhost:8000/api/posts?column=title&operator=contains&value=hello
        if ($request && $this->hasSearchQuery($request)) {
            $builder = $this->buildSearchQuery($builder, $request);
        }

        // Turn on the flexibility for the programmer to apply his own query to chain on the current query then we will retrieve back the query builder after the programmer applies his logic and proceed our own queries.
        if ($callback) {
            $builder = $callback($builder);
            throw_unless($builder, new EloquentBuilderWasSetToNullException);
        }

        // we will try to parse the query and return the output of it, if anything goes wrong, by default we will be returning an empty collection.
        try {
            // if the request doesn't have a limit, it will return null, and since limit takes an integer value >= 0, then it won't limit
            // at all since we will replace the null with a negative number.
            return $builder->select(...$this->getSelectableColumns())->limit(
                $request->limit ?? -1
            )->get();
        } catch (QueryException $e) {
            return collect([]);
        }
    }

    /**
     * @return mixed
     */
    public function getSelectableColumns(): array
    {
        if (method_exists($model = $this->getModel(), 'getSelectableColumns')) {
            return $model->getSelectableColumns();
        }

        return $this->getDisplayableColumns();
    }

    /**
     * Get The Table Name.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->getModel()->getTable();
    }

    /**
     * @return array
     */
    public function getUpdatableColumns(): array
    {
        if (method_exists($this->getModel(), 'getUpdatableColumns')) {
            return $this->getColumnsWithoutPrimaryKey($this->getModel()->getUpdatableColumns());
        }

        return $this->getColumnsWithoutPrimaryKey($this->getDisplayableColumns());
    }

    /**
     * Get query source of dataTable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract public function query(): Builder;

    /**
     * Return the response skeleton.
     *
     * @return array
     */
    public function response(callable $callback = null): array
    {
        return [
            'table' => $this->getTable(),
            'displayable' => $this->getDisplayableColumns(),
            'records' => $this->getRecords(request(), $callback)->load((array) $this->relations),
            'updatable' => $this->getUpdatableColumns(),
            'custom_columns' => $this->getCustomColumnNames(),
            'allow' => [
                'creatable' => $this->allowCreating ?? false,
                'deletable' => $this->allowDeleting ?? false,
                'updatable' => $this->allowUpdating ?? false,
            ],
        ];
    }

    /**
     * Build Search Query.
     *
     * @param  Builder $builder
     * @param  Request $request
     * @return Builder
     */
    protected function buildSearchQuery(Builder $builder, Request $request): Builder
    {
        ['operator' => $operator, 'value' => $value] = $this->resolveQueryParts($request->operator, $request->value);

        throw_unless(in_array($request->column, $this->getDisplayableColumns()), InvalidColumnSearchException::class);

        return $builder->where($request->column, $operator, $value);
    }

    /**
     * Get Database Column Names.
     *
     * @return array
     */
    protected function getDatabaseColumnNames(): array
    {
        return Schema::getColumnListing($this->getTable());
    }

    /**
     * Check if the request has a search query.
     *
     * @param \Illuminate\Http\Request $request.
     * @return bool
     */
    protected function hasSearchQuery(Request $request): bool
    {
        return count(array_filter($request->only(['column', 'operator', 'value']))) === 3;
    }

    /**
     * Resolve Query Parts.
     *
     * @param string $operator
     * @param string $value
     *
     * @return array
     */
    protected function resolveQueryParts(string $operator, string $value): array
    {
        return Arr::get([
            'equals' => [
                'operator' => '=',
                'value' => $value,
            ],
            'contains' => [
                'operator' => 'LIKE',
                'value' => "%{$value}%",
            ],
            'starts_with' => [
                'operator' => 'LIKE',
                'value' => "{$value}%",
            ],
            'ends_with' => [
                'operator' => 'LIKE',
                'value' => "%{$value}",

            ],
            'greater_than' => [
                'operator' => '>',
                'value' => $value,

            ],
            'less_than' => [
                'operator' => '<',
                'value' => $value,

            ],
        ], $operator);
    }
}
