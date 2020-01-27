<?php

namespace Laravel\DataTables\Tests\Services;

use Illuminate\Database\Eloquent\Builder;
use Laravel\DataTables\Tests\Models\User;
use Laravel\DataTables\Services\BaseDataTableService;

class UserDataTableService extends BaseDataTableService
{
    /**
     * @var mixed
     */
    protected $users;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {

        $this->users = $user;
    }

    /**
     * @return mixed
     */
    public function query(): Builder
    {
        return $this->users->query();
    }
}
