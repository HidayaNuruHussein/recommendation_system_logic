<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'nuruhidaya9@gmail.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password1'),
                'role' => 'customer',
            ]
        );

        User::updateOrCreate(
            ['email' => 'hngoby@egmail.com'],
            [
                'name' => 'SHAMIRA NURU',
                'password' => bcrypt('44242444'),
                'role' => 'seller',
            ]
        );

        // Admin Seeder
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $this->call([
            CategorySeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
