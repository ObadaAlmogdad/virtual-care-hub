<?php

namespace App\Repositories;

use App\Models\Complaint;
use App\Repositories\Interfaces\ComplaintRepositoryInterface;

class ComplaintRepository implements ComplaintRepositoryInterface
{
    protected $model;

    public function __construct(Complaint $model)
    {
        $this->model = $model;
    }

    public function getComplaintsByType(string $type)
    {
        return $this->model->where('type', $type)->get();
    }

    public function countComplaintsByType(string $type): int
    {
        return $this->model->where('type', $type)->count();
    }

}
