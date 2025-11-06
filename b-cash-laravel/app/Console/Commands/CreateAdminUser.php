<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin {phone_number} {email} {full_name} {password} {pin}';
    protected $description = 'Create a new admin user';

    public function handle()
    {
        $user = User::create([
            'phone_number' => $this->argument('phone_number'),
            'email' => $this->argument('email'),
            'full_name' => $this->argument('full_name'),
            'password_hash' => Hash::make($this->argument('password')),
            'pin_hash' => Hash::make($this->argument('pin')),
            'is_verified' => true,
            'is_admin' => true,
            'login_attempts' => 0,
        ]);

        // Create wallet for admin
        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'currency' => 'USD',
        ]);

        $this->info('Admin user created successfully!');
    }
}