<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Wallet;
use App\Models\User;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Create wallet for each user with random balance
            Wallet::create([
                'user_id' => $user->id,
                'balance' => rand(100, 1000), // Random balance between 100-1000
            ]);
        }
    }
}
