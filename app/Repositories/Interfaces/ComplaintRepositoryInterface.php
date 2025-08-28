<?php

namespace App\Repositories\Interfaces;


interface ComplaintRepositoryInterface
{
    public function getComplaintsByType(string $type);
    public function countComplaintsByType(string $type): int;

}
