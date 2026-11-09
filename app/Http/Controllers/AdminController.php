<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Review;


class AdminController extends Controller
{

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
        $listings = Listing::with(['category', 'provider'])->get();
        return view('admin.listings.index', compact('listings'));
    }

    public function editListing($id)
    {
        $listing = Listing::findOrFail($id);
        return view('admin.listings.edit', compact('listing'));
    }

    public function updateListing(Request $request, $id)
    {
        $listing = Listing::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|string',
            'is_featured' => 'boolean',
        ]);

        $listing->update([
            'title' => $request->title,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured'),
        ]);

        return redirect()->route('admin.listings.index')->with('success', 'Listing updated successfully.');
    }

    public function destroyListing($id)
    {
        Listing::findOrFail($id)->delete();
        return redirect()->route('admin.listings.index')->with('success', 'Listing deleted successfully.');
    }

    public function listingReviews($id)
    {
        $listing = Listing::findOrFail($id);
        // Load reviews with their authors
        $reviews = Review::with('author')->where('listing_id', $id)->latest()->get();
        
        return view('admin.listings.reviews', compact('listing', 'reviews'));
    }

    public function toggleReviewVisibility($id)
    {
        $review = Review::findOrFail($id);
        
        // Flip the boolean value (true becomes false, false becomes true)
        $review->update(['is_hidden' => !$review->is_hidden]);

        $status = $review->is_hidden ? 'hidden' : 'visible';
        return back()->with('success', "Review is now {$status}.");
    }

    public function destroyReview($id)
    {
        Review::findOrFail($id)->delete();
        return back()->with('success', 'Review deleted successfully.');
    }
}