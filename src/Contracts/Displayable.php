<?php

namespace Laravel\DataTables\Contracts;

interface Displayable
{
    /**
     * Get Custom Column Names.
     *
     * @return array
     */
    public function getCustomColumnNames(): array;

    /**
     * Get displayable columns.
     *
     * @return array
     */
    public function getDisplayableColumns(): array;

    /**
     * Get the columns that user can see at the frontend to update.
     *
     * @return array
     */
    public function getUpdatableColumns(): array;
}
