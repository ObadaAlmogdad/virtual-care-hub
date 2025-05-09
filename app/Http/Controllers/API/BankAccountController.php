<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BankAccountService;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BankAccountController extends Controller
{
    protected BankAccountService $bankAccountService;

    public function __construct(BankAccountService $bankAccountService)
    {
        $this->bankAccountService = $bankAccountService;
    }

    public function link(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        try {
            $bankAccount = $this->bankAccountService->linkBankAccount($user, $request->all());
            return response()->json(['bank_account' => $bankAccount], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
