<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Enums\UserRole;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'athr@school.com',
            ],
            [
                'name' => 'Athr Owner',
                'username' => 'owner',
                'role' => UserRole::Owner,
                'is_active' => true,
                'language' => 'ar',
                'theme' => 'light',
                'timezone' => 'Asia/Riyadh',
                'email_verified_at' => now(),
                'password' => Hash::make('123456789'),
            ]
        );
    }
}
