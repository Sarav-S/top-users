<?php

use App\Models\User;
// use DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/via-redis', function () {
    return User::frequentPosters();
});

Route::get('/via-db', function () {
    return cache()->get('top_users_data');
});
