<?php

namespace App\Services;

use App\Repositories\Interfaces\ActivationRequestRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\ActivationRequest;
use App\Models\User;

class ActivationRequestService
{
    protected ActivationRequestRepositoryInterface $activationRequestRepository;

    public function __construct(ActivationRequestRepositoryInterface $activationRequestRepository)
    {
        $this->activationRequestRepository = $activationRequestRepository;
    }

    public function sendRequest(User $user): ActivationRequest
    {
        $data = [
            'user_id' => $user->id,
            'status' => 'pending',
        ];
        return $this->activationRequestRepository->create($data);
    }

    public function approveRequest(ActivationRequest $activationRequest, $adminId, $notes = null): bool
    {
        $data = [
            'status' => 'approved',
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'notes' => $notes,
        ];
        $activationRequest->user->is_activated = true;
        $activationRequest->user->save();
        return $this->activationRequestRepository->update($activationRequest, $data);
    }

    public function getStatus(User $user): ?ActivationRequest
    {
        return $this->activationRequestRepository->findLatestByUserId($user->id);
    }
} 