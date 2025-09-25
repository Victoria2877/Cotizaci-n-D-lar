<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DolarController;

Route::get('/cotizacion', [DolarController::class, 'apiCotizacion']);
