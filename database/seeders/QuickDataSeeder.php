<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuickDataSeeder extends Seeder
{
    /**
     * Run the database seeds for quick testing.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting quick data population...');
        
        // Run essential seeders in order
        $this->call([
            PlanSeeder::class,
            DoctorSpecialtySeeder::class,
            SubscriptionSeeder::class,
            TransactionSeeder::class,
        ]);
        
        $this->command->info('✅ Quick data population completed!');
        $this->command->info('📊 You can now test:');
        $this->command->info('   - Plans: /api/plans');
        $this->command->info('   - Subscriptions: /api/admin/subscriptions');
        $this->command->info('   - Financial Summary: /api/admin/financial/summary');
        $this->command->info('   - Monthly Revenue: /api/admin/financial/monthly-revenue');
        $this->command->info('   - Revenue by Specialty: /api/admin/financial/revenue-by-specialty');
        $this->command->info('   - Transaction Records: /api/admin/financial/transactions');
    }
}
