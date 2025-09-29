<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        // DB::table('users')->insert([
        //     'name' => 'Lecture Account',
        //     'email' => 'dosen@example.com',
        //     'password' => Hash::make('dosen654'),
        //     'role' => 'dosen',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // DB::table('users')->insert([
        //     'name' => 'Dosen 2',
        //     'email' => 'dosen2@example.com',
        //     'password' => Hash::make('dosen123'),
        //     'role' => 'dosen',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // DB::table('users')->insert([
        //     'name' => 'Admin',
        //     'email' => 'admin@gmail.com',
        //     'password' => Hash::make('admin123'),
        //     'role' => 'admin',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);
    }
}
