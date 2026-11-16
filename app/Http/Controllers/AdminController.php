<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\User;
use App\Services\ListingService;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        protected ListingService $listingService,
        protected ReviewService $reviewService
    ) {
    }

    public function dashboard()
    {
        // Get some basic stats for the dashboard
        $stats = [
            'users' => User::count(),
            'listings' => Listing::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function index()
    {
        // Eager load listings so $user->listings->count() is efficient
        $users = User::with('listings')->get(); 
        
        return view('admin.users.index', compact('users'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user')); 
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$id,
            'role' => 'required|in:admin,user',
            'city' => 'nullable|string|max:255',
        ]);

        $user->update($request->only('name', 'email', 'role', 'city'));

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete yourself.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    // --- LISTING MANAGEMENT ---

    public function listingsIndex()
    {
        $listings = $this->listingService->getAllListingsForAdmin();
        return view('admin.listings.index', compact('listings'));
    }

    public function editListing($id)
    {
        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            abort(404);
        }

        return view('admin.listings.edit', compact('listing'));
    }

    public function updateListing(Request $request, $id)
    {
        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|string',
            'is_featured' => 'boolean',
        ]);

        $this->listingService->updateListing($id, [
            'title' => $request->title,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured'),
        ], auth()->id());

        return redirect()->route('admin.listings.index')->with('success', 'Listing updated successfully.');
    }

    public function destroyListing($id)
    {
        $deleted = $this->listingService->deleteListing($id, auth()->id());
        if (!$deleted) {
            abort(404);
        }

        return redirect()->route('admin.listings.index')->with('success', 'Listing deactivated successfully.');
    }

    public function listingReviews($id)
    {
        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            abort(404);
        }

        $reviews = $this->reviewService->getReviewsForListing($id, visibleOnly: false);

        return view('admin.listings.reviews', compact('listing', 'reviews'));
    }

    public function toggleReviewVisibility($id)
    {
        $review = $this->reviewService->toggleVisibility($id, auth()->id());
        if (!$review) {
            abort(404);
        }

        $status = $review->is_hidden ? 'hidden' : 'visible';
        return back()->with('success', "Review is now {$status}.");
    }

    public function destroyReview($id)
    {
        $hidden = $this->reviewService->hideReview($id, auth()->id());
        if (!$hidden) {
            abort(404);
        }

        return back()->with('success', 'Review hidden successfully.');
    }
}