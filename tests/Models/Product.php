<?php

namespace Laravel\DataTables\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'stock',
    ];

    public function getUpdatableColumns()
    {
        return [
            'name',
            'stock',
        ];
    }
}
