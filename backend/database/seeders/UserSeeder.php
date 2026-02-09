<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('users')->insert([
            [
                'email' => 'iker@demo.com',
                'password' => Hash::make('password'),
                'role' => 'alumno',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'naia@demo.com',
                'password' => Hash::make('password'),
                'role' => 'alumno',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'prueba@test.com',
                'password' => Hash::make('12345'),
                'role' => 'alumno',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'danel.tutor@demo.com',
                'password' => Hash::make('password'),
                'role' => 'tutor_egibide',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'eneko.empresa@demo.com',
                'password' => Hash::make('password'),
                'role' => 'tutor_empresa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'admin@demo.com',
                'password' => Hash::make('admin'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
