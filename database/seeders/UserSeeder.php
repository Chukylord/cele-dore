<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@virtisone.com'],
            [
                'name' => 'Admin Vir Tisone',
                'rol' => 'admin',
                'password' => bcrypt('Virtisone2026!'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'empleada@virtisone.com'],
            [
                'name' => 'Empleada Vir Tisone',
                'rol' => 'empleada',
                'password' => bcrypt('12345678'),
            ]
        );
    }
}