<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

// Autentificare API (Sanctum)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Endpoint-uri publice de citire pentru client-side
Route::get('/home', [ListingController::class, 'home']);
Route::get('/categories', [ListingController::class, 'categories']);
Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{id}', [ListingController::class, 'show'])->whereNumber('id');
Route::get('/plans', [ListingController::class, 'plans']);
Route::get('/reviews', [ReviewController::class, 'index']);

// Operații protejate pentru utilizatorul autentificat
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/my-listings', [ListingController::class, 'myListings']);
    Route::get('/listings/{id}/own', [ListingController::class, 'showOwn'])->whereNumber('id');
    Route::post('/listings', [ListingController::class, 'store']);
    Route::put('/listings/{id}', [ListingController::class, 'update'])->whereNumber('id');
    Route::post('/listings/{id}/promote', [ListingController::class, 'promote'])->whereNumber('id');
    Route::post('/plans/select', [ListingController::class, 'selectPlan']);
    Route::patch('/listings/{id}/toggle-status', [ListingController::class, 'toggleStatus'])->whereNumber('id');
    Route::delete('/listings/{id}', [ListingController::class, 'destroy'])->whereNumber('id');
    Route::delete('/listings/{id}/images/{imageId}', [ListingController::class, 'destroyImage'])->whereNumber(['id', 'imageId']);

    Route::post('/listings/{id}/reviews', [ReviewController::class, 'store'])->whereNumber('id');
    Route::put('/reviews/{id}', [ReviewController::class, 'update'])->whereNumber('id');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->whereNumber('id');

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])->whereNumber('id');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
});
