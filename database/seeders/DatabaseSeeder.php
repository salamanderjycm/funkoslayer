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
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@funkos.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        // Create customer user
        User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@funkos.com',
            'password' => 'password123',
            'role' => 'customer',
        ]);

        // Run seeders for categories and products
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
