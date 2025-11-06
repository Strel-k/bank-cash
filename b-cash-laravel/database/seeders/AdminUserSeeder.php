<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'phone_number' => '1234567890',
            'email' => 'admin@bcash.com',
            'full_name' => 'System Administrator',
            'password_hash' => Hash::make('admin123'),
            'pin_hash' => Hash::make('1234'),
            'is_verified' => true,
            'is_admin' => true,
            'login_attempts' => 0,
            'email_verified_at' => now(),
        ]);

        // Create wallet for admin
        Wallet::create([
            'user_id' => $admin->id,
            'balance' => 0,
            'currency' => 'USD',
        ]);
    }
}
