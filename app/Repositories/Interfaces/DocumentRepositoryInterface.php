<?php

namespace App\Repositories\Interfaces;

use App\Models\Document;

interface DocumentRepositoryInterface
{
    public function create(array $data): Document;
} 