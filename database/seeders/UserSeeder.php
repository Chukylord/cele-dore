<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@fnpeluqueria.com'],
            [
                'name' => 'Admin fn peluqueria',
                'rol' => 'admin',
                'password' => bcrypt('Flopine2026!'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'empleada@fnpeluqueria.com'],
            [
                'name' => 'Empleada fn peluqueria',
                'rol' => 'empleada',
                'password' => bcrypt('12345678'),
            ]
        );
    }
}