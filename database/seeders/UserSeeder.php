<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed users.
     */
    public function run(): void
    {
        // Ensure a few demo users exist for local development.
        User::updateOrCreate(
            ['email' => 'demo1@example.com'],
            [
                'name' => 'Demo User 1',
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]
        );

        User::updateOrCreate(
            ['email' => 'demo2@example.com'],
            [
                'name' => 'Demo User 2',
                'password' => bcrypt('password'),
                'role' => 'seller',
            ]
        );
    }
}
