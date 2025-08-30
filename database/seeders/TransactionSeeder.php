<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Appointment;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get wallets
        $wallets = Wallet::all();
        $doctors = Doctor::with('user')->get();
        $appointments = Appointment::all();

        if ($wallets->isEmpty() || $doctors->isEmpty()) {
            return;
        }

        // Create appointment payment transactions
        for ($i = 0; $i < 100; $i++) {
            $wallet = $wallets->random();
            $doctor = $doctors->random();
            $doctorWallet = Wallet::firstOrCreate(['user_id' => $doctor->user_id], ['balance' => 0]);
            
            $transactionDate = Carbon::now()->subDays(rand(1, 90));
            $amount = $this->getRandomAmount();
            
            // Create patient payment transaction
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'payment',
                'amount' => $amount,
                'reference_id' => $appointments->isNotEmpty() ? $appointments->random()->id : null,
                'reference_type' => 'App\Models\Appointment',
                'description' => 'Appointment payment',
                'created_at' => $transactionDate,
                'updated_at' => $transactionDate,
            ]);

            // Create doctor charge transaction
            Transaction::create([
                'wallet_id' => $doctorWallet->id,
                'type' => 'charge',
                'amount' => $amount,
                'reference_id' => $appointments->isNotEmpty() ? $appointments->random()->id : null,
                'reference_type' => 'App\Models\Appointment',
                'description' => 'Appointment payment received',
                'created_at' => $transactionDate,
                'updated_at' => $transactionDate,
            ]);

            // Update wallet balances
            $wallet->balance = max(0, $wallet->balance - $amount);
            $wallet->save();
            
            $doctorWallet->balance += $amount;
            $doctorWallet->save();
        }

        // Create some wallet topup transactions
        for ($i = 0; $i < 30; $i++) {
            $wallet = $wallets->random();
            $amount = rand(50, 500);
            $transactionDate = Carbon::now()->subDays(rand(1, 60));
            
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'charge',
                'amount' => $amount,
                'reference_id' => 'TOPUP_' . rand(1000, 9999),
                'reference_type' => 'WalletTopup',
                'description' => 'Wallet topup',
                'created_at' => $transactionDate,
                'updated_at' => $transactionDate,
            ]);

            $wallet->balance += $amount;
            $wallet->save();
        }

        // Create some subscription payment transactions
        $this->createSubscriptionTransactions();
    }

    private function getRandomAmount(): float
    {
        $amounts = [25.00, 30.00, 35.00, 40.00, 45.00, 50.00, 55.00, 60.00, 75.00, 80.00, 100.00];
        return $amounts[array_rand($amounts)];
    }

    private function createSubscriptionTransactions(): void
    {
        $wallets = Wallet::all();
        $plans = \App\Models\Plan::all();

        if ($wallets->isEmpty() || $plans->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 20; $i++) {
            $wallet = $wallets->random();
            $plan = $plans->random();
            $transactionDate = Carbon::now()->subDays(rand(1, 45));
            
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'payment',
                'amount' => $plan->price,
                'reference_id' => $plan->id,
                'reference_type' => 'App\Models\Plan',
                'description' => 'Plan subscription purchase',
                'created_at' => $transactionDate,
                'updated_at' => $transactionDate,
            ]);

            $wallet->balance = max(0, $wallet->balance - $plan->price);
            $wallet->save();
        }
    }
}
