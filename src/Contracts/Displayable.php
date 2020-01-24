<?php

namespace Laravel\DataTables\Contracts;

interface Displayable
{
    public function getCustomColumnNames();

    public function getDisplayableColumns();

    public function getUpdatableColumns();
}
