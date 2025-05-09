<?php

namespace App\Repositories\Interfaces;

use App\Models\ActivationRequest;

interface ActivationRequestRepositoryInterface
{
    public function create(array $data): ActivationRequest;
    public function findById($id): ?ActivationRequest;
    public function update(ActivationRequest $activationRequest, array $data): bool;
    public function findLatestByUserId($userId): ?ActivationRequest;
} 