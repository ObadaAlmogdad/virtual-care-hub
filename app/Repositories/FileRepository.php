<?php

namespace App\Repositories;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class FileRepository
{
    protected $model;

    public function __construct(File $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function storeFile($file, $directory)
    {
        $path = $file->store($directory, 'public');
        return [
            'path' => $path,
            'origanName' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension()
        ];
    }

    public function getFileUrl($path)
    {
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }
        return null;
    }

    public function fileExists($path)
    {
        return Storage::disk('public')->exists($path);
    }
} 