<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/run-dosen-seeder', function () {
    Artisan::call('db:seed', [
        '--class' => 'DosenSeeder',
        '--force' => true,
    ]);
    return "DosenSeeder executed!";
});
