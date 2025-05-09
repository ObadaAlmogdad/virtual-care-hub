<?php

namespace App\Services;

use App\Repositories\Interfaces\BankAccountRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\BankAccount;
use App\Models\User;

class BankAccountService
{
    protected BankAccountRepositoryInterface $bankAccountRepository;

    public function __construct(BankAccountRepositoryInterface $bankAccountRepository)
    {
        $this->bankAccountRepository = $bankAccountRepository;
    }

    public function linkBankAccount(User $user, array $data): BankAccount
    {
        $validator = Validator::make($data, [
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'iban' => 'required|string',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data['user_id'] = $user->id;
        $bankAccount = $this->bankAccountRepository->create($data);
        $user->bank_account_id = $bankAccount->id;
        $user->save();
        return $bankAccount;
    }
} 