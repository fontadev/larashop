<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coupon::create([
            'code' => 'DESCONTO10',
            'type' => 'percentage',
            'value' => 10,
            'min_value' => 100,
            'expires_at' => now()->addMonths(1),
            'active' => true,
            'usage_limit' => 100,
            'used_count' => 0,
        ]);

        Coupon::create([
            'code' => 'DESCONTO15',
            'type' => 'percentage',
            'value' => 15,
            'min_value' => 200,
            'expires_at' => now()->addMonths(1),
            'active' => true,
            'usage_limit' => 50,
            'used_count' => 0,
        ]);

        Coupon::create([
            'code' => 'MENOS30REAIS',
            'type' => 'fixed',
            'value' => 30,
            'min_value' => 150,
            'expires_at' => now()->addMonths(1),
            'active' => true,
            'usage_limit' => 30,
            'used_count' => 0,
        ]);

        Coupon::create([
            'code' => 'FRETEGRATIS',
            'type' => 'fixed',
            'value' => 20,
            'min_value' => 120,
            'expires_at' => now()->addMonths(1),
            'active' => true,
            'usage_limit' => 40,
            'used_count' => 0,
        ]);

        Coupon::create([
            'code' => 'VENCIDO20',
            'type' => 'percentage',
            'value' => 20,
            'min_value' => 50,
            'expires_at' => now()->subDays(10),
            'active' => true,
            'usage_limit' => 100,
            'used_count' => 0,
        ]);

        Coupon::create([
            'code' => 'INATIVO25',
            'type' => 'percentage',
            'value' => 25,
            'min_value' => 80,
            'expires_at' => now()->addMonths(1),
            'active' => false,
            'usage_limit' => 100,
            'used_count' => 0,
        ]);
    }
}
