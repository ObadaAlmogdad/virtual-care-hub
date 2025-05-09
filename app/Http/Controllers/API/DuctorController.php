<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\DuctorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DuctorController extends Controller
{
    protected $ductorService;

    public function __construct(DuctorService $ductorService)
    {
        $this->ductorService = $ductorService;
    }

}
