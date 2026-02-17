<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'family_name' => 'Administrator',
                'given_name' => 'System',
                'middle_name' => null,
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'approved',
            ]
        );
    }
}
