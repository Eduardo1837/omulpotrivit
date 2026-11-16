<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ListingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Operațiile API pentru entitatea Listing (GET, GET(id), POST, PUT)
Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{id}', [ListingController::class, 'show']);
Route::post('/listings', [ListingController::class, 'store']);
Route::put('/listings/{id}', [ListingController::class, 'update']);

// Planuri de promovare (public) și controale utilizator autentificat
Route::get('/plans', [ListingController::class, 'plans']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/listings/{id}/promote', [ListingController::class, 'promote']);
    Route::patch('/listings/{id}/toggle-status', [ListingController::class, 'toggleStatus']);
    Route::delete('/listings/{id}', [ListingController::class, 'destroy']);
});
