<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates or updates local development and testing accounts.
     */
    public function run(): void
    {
        // Safety guard: only create predictable test accounts in local/testing environments.
        if (! app()->environment('local', 'testing')) {
            $this->command->warn('UserSeeder skipped: not in local or testing environment.');
            return;
        }

        // 1. Admin Account (Full access to Admin panel at /admin)
        User::updateOrCreate(
            ['email' => 'admin@alexa.test'],
            [
                'name' => 'Alexa Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Customer Account (Standard customer for catalog, cart, checkout)
        User::updateOrCreate(
            ['email' => 'customer@alexa.test'],
            [
                'name' => 'Alexa Customer',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );
    }
}

