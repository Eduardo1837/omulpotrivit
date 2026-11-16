<?php

namespace App\Http\Controllers;

use App\Services\ListingService;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewService $reviewService,
        protected ListingService $listingService
    ) {
    }

    public function index()
    {
        $reviews = $this->reviewService->getVisibleReviewsPaginated(10);

        return view('reviews.index', compact('reviews'));
    }

    public function create()
    {
        $listings = $this->listingService->getAllActiveListings();

        return view('reviews.create', compact('listings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => ['required', 'exists:listings,id'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'comment'    => ['required', 'string', 'max:1000'],
        ]);

        $authorId = auth()->id();
        $existing = $this->reviewService->getReviewsForListing($validated['listing_id'], visibleOnly: false)
            ->firstWhere('author_id', $authorId);

        if ($existing) {
            $redirect = $request->boolean('redirect_to_listing')
                ? redirect()->route('listings.details', $validated['listing_id'])
                : back();

            return $redirect->with('error', 'Ai lăsat deja o recenzie pentru acest anunț.');
        }

        $this->reviewService->createReview($validated, $authorId);

        if ($request->boolean('redirect_to_listing')) {
            return redirect()->route('listings.details', $validated['listing_id'])
                ->with('success', 'Recenzia a fost adăugată cu succes!');
        }

        return redirect()->route('reviews.index')
            ->with('success', 'Review submitted successfully!');
    }
}
