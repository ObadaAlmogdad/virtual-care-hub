<?php

namespace App\Services;

use App\Repositories\Interfaces\ComplaintRepositoryInterface;

class ComplaintService
{
    protected $complaintRepository;

    public function __construct(ComplaintRepositoryInterface $complaintRepository)
    {
        $this->complaintRepository = $complaintRepository;
    }

    public function getComplaintsByType(string $type)
    {
        $complaints = $this->complaintRepository->getComplaintsByType($type);
        $count = $this->complaintRepository->countComplaintsByType($type);

        return [
            'type' => $type,
            'count' => $count,
            'complaints' => $complaints,
        ];
    }
}
