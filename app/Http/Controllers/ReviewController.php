<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Review;
use App\Models\Listing;

class ReviewController extends Controller
{
    public function index()
    {
        // Eager load relationships to prevent N+1 queries
        $reviews = Review::with(['listing', 'author'])
            ->latest()
            ->paginate(10);

        return view('reviews.index', compact('reviews'));
    }

    public function create()
    {
        $listings = Listing::all();

        return view('reviews.create', compact('listings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => ['required', 'exists:listings,id'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'comment'    => ['required', 'string', 'max:1000'],
        ]);

        // Assign the currently authenticated user as the author
        $validated['author_id'] = 1; // Replace with auth()->id() in a real application

        Review::create($validated);

        return redirect()->route('reviews.index')
            ->with('success', 'Review submitted successfully!');
    }
}
