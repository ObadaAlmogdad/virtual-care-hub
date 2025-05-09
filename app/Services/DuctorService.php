<?php

namespace App\Services;

use App\Repositories\Interfaces\DuctorRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DuctorService
{
    protected $userRepository;

    public function __construct(DuctorRepositoryInterface $ductorRepository)
    {
        $this->userRepository = $ductorRepository;
    }


} 