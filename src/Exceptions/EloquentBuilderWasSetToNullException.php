<?php

namespace Laravel\DataTables\Exceptions;

use Exception;

class EloquentBuilderWasSetToNullException extends Exception
{
    /**
     * @var string
     */
    public $message = 'You have set your Eloquent query builder to null, perhaps you forgot to return the builder at your callback.';
}
