<?php

use App\Http\Controllers\Api\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/stores/nearby', [StoreController::class, 'nearby']);
Route::get('/stores/can-deliver', [StoreController::class, 'canDeliver']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/stores', [StoreController::class, 'store']);
});
