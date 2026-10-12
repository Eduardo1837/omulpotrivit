<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\Category;
use App\Models\Listing;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/laborator', function () {
    // Extragem toate categoriile
    $categories = Category::all();
    
    // Extragem anunțurile, aducând automat și datele despre prestator și categorie (Eager Loading)
    $listings = Listing::with(['provider', 'category'])->get();

    return view('laborator', [
        'categories' => $categories,
        'listings' => $listings
    ]);
});

require __DIR__.'/auth.php';
