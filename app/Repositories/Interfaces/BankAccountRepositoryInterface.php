<?php

namespace App\Repositories\Interfaces;

use App\Models\BankAccount;

interface BankAccountRepositoryInterface
{
    public function create(array $data): BankAccount;
} 