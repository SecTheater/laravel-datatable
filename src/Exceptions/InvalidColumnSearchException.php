<?php

namespace Laravel\DataTables\Exceptions;

use Exception;

class InvalidColumnSearchException extends Exception
{
    /**
     * @var string
     */
    public $message = 'most probably, you are trying to search with a column that does not exist at the table.';
}
