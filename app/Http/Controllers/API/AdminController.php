<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdminService;


class AdminController extends Controller
{

    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function verficat($id)
    {
        try {
            $this->adminService->verficatAccount($id);

            return response()->json([
                'status' => 1,
                'message' => 'account verificating successfully',
            ], 200);
        } catch (\Exception $err) {
            return response()->json([
                'status' => 0,
                'message' => $err->getMessage(),
            ], 400);
        }
    }
}
