<?php

namespace App\Services;

use App\Repositories\Interfaces\DocumentRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use App\Models\Document;

class DocumentService
{
    protected DocumentRepositoryInterface $documentRepository;

    public function __construct(DocumentRepositoryInterface $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    public function uploadDocument(array $data, UploadedFile $file): Document
    {
        $validator = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
            'document_type' => 'required|string',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $path = $file->store('documents', 'public');
        $data['file_path'] = $path;
        return $this->documentRepository->create($data);
    }
} 