<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ReviewController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Tag;
use App\Accessors\CategoryAccessor;
use App\Accessors\ListingAccessor;
use App\ViewModels\ListingViewModel;

// ----------------------------------------------------
// Public Routes
// ----------------------------------------------------
Route::get('/', function () {
   $rawListings = Listing::with('category')->latest()->take(6)->get();

   $recentListings = $rawListings->map(function ($listing) {
        return new ListingViewModel($listing);
    });

    $categories = Category::all();


   return view('welcome', compact('recentListings', 'categories'));
})->name('home');

Route::get('/laborator', function () {
    $categoryAccessor = new CategoryAccessor();
    $listingAccessor  = new ListingAccessor();
    
    $categoriesEager = $categoryAccessor->getAll();
    $categoriesLazy = Category::all();
    $listingsFromCategory = $listingAccessor->getByCategoryId(1);
    $tags = Tag::all();

    return view('laborator', [
        'categoriesEager'      => $categoriesEager,
        'categoriesLazy'       => $categoriesLazy,
        'listingsFromCategory' => $listingsFromCategory,
        'tags'                 => $tags,
    ]);
});

Route::get('/listings', [ListingController::class, 'index'])->name('listings.index');
Route::get('/listings/{id}', [ListingController::class, 'details'])->name('listings.details');
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');

// ----------------------------------------------------
// Authenticated Routes (Requires Login)
// ----------------------------------------------------
Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['verified'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/listings/create', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/listings', [ListingController::class, 'store'])->name('listings.store');
    Route::get('/listings/{id}/edit', [ListingController::class, 'edit'])->name('listings.edit');
    Route::post('/listings/{id}/edit', [ListingController::class, 'update'])->name('listings.update');

    Route::get('/reviews/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // ----------------------------------------------------
    // Admin Routes
    // ----------------------------------------------------
   Route::middleware(AdminMiddleware::class)->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Manage Users
        Route::get('/users', [AdminController::class, 'index'])->name('users.index');
        Route::get('/users/{id}/edit', [AdminController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        
        // Manage Listings
        Route::get('/listings', [AdminController::class, 'listingsIndex'])->name('listings.index');
        Route::get('/listings/{id}/edit', [AdminController::class, 'editListing'])->name('listings.edit');
        Route::put('/listings/{id}', [AdminController::class, 'updateListing'])->name('listings.update');
        Route::delete('/listings/{id}', [AdminController::class, 'destroyListing'])->name('listings.destroy');

        // Manage Reviews for a Listing
        Route::get('/listings/{id}/reviews', [AdminController::class, 'listingReviews'])->name('listings.reviews');
        Route::patch('/reviews/{id}/toggle-visibility', [AdminController::class, 'toggleReviewVisibility'])->name('reviews.toggle');
        Route::delete('/reviews/{id}', [AdminController::class, 'destroyReview'])->name('reviews.destroy');
    });
        
});

require __DIR__.'/auth.php';