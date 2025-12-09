<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'phone' => '081122456',
            'email' => 'admin@ea-affiliate.com',
            'password' => Hash::make('@dm1nea'), // Ganti dengan password yang aman
            'role' => 'admin',
            'is_active' => true,
        ]);

        echo "Admin user created successfully!\n";
        echo "Phone: 081122456\n";
        echo "Password: password\n";
        echo "Role: admin\n";
    }
}
