<?php

namespace App\Repositories;

use App\Models\Doctor;

class DoctorRepository
{
    protected $model;

    public function __construct(Doctor $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }
} 