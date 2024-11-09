<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index.index');
});


Route::post('/submit', [FileController::class, 'submitFile']);