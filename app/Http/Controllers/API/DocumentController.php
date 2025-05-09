<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DocumentService;
use Illuminate\Validation\ValidationException;

class DocumentController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function upload(Request $request, $userId)
    {
        try {
            $data = [
                'user_id' => $userId,
                'document_type' => $request->input('document_type'),
            ];
            $file = $request->file('file');
            $document = $this->documentService->uploadDocument($data, $file);
            return response()->json(['document' => $document], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
