<?php

namespace Laravel\DataTables\Contracts;

interface Displayable
{
    public function getCustomColumnNames() : array;

    public function getDisplayableColumns() : array;

    public function getUpdatableColumns() : array;
}
