<?php

use App\Http\Controllers\Api\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/stores/nearby', [StoreController::class, 'nearby']);

