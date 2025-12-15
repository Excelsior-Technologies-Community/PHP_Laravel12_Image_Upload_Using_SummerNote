<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('posts/create',[PostController::class,'create']);
Route::post('posts/store',[PostController::class,'store'])->name('posts.store');

Route::get('/', function () {
    return view('welcome');
});
