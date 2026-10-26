<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Tag;
use App\Accessors\CategoryAccessor;
use App\Accessors\ListingAccessor;

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

    $categoryAccessor = new CategoryAccessor();
    $listingAccessor  = new ListingAccessor();
    // Extragem toate categoriile

    // EAGER LOADING — via Accessor (Category cu listings incluse)
    $categoriesEager = $categoryAccessor->getAll();

    // LAZY LOADING — Category::all() fără with()
    $categoriesLazy = Category::all();
    
    // Extragem anunțurile, aducând automat și datele despre prestator și categorie (Eager Loading)
    $listingsFromCategory = $listingAccessor->getByCategoryId(1);

    $tags = Tag::all();

    return view('laborator', [
        'categoriesEager'      => $categoriesEager,
        'categoriesLazy'       => $categoriesLazy,
        'listingsFromCategory' => $listingsFromCategory,
        'tags' => $tags,
    ]);
});

require __DIR__.'/auth.php';
