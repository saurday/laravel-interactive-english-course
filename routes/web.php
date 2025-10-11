<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');

// });

    Route::get('/login', fn () => response('Please login', 401))->name('login');

