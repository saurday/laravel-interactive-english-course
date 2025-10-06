<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        // Akun dosen utama
        DB::table('users')->updateOrInsert(
            ['email' => 'dosen@example.com'],
            [
                'name' => 'Lecture Account',
                'password' => Hash::make('dosen654'),
                'role' => 'dosen',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // Akun dosen kedua
        DB::table('users')->updateOrInsert(
            ['email' => 'dosen2@example.com'],
            [
                'name' => 'Dosen 2',
                'password' => Hash::make('dosen123'),
                'role' => 'dosen',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // Akun admin
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
