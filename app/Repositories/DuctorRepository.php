<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\DuctorRepositoryInterface;

use Illuminate\Support\Facades\Cache;

class DuctorRepository implements DuctorRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

}
