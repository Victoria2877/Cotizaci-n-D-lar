<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DolarController;

Route::get('/', [DolarController::class, 'index'])->name('home');

// Endpoint JSON para tu botón "Ver JSON API" y para refrescar con JS
Route::get('/api/cotizaciones', [DolarController::class, 'json'])->name('api.cotizaciones');
