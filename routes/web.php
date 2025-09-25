<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DolarController;

Route::get('/', [DolarController::class, 'cotizacion']);
Route::get('/dolar', [DolarController::class, 'cotizacion']);

