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
        $userId = auth()->id();
        $listings = $this->listingService->getAllActiveListings()
            ->reject(fn ($listing) => (int) $listing->provider_id === (int) $userId);

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
        $listing = $this->listingService->getActiveListingById($validated['listing_id']);

        if ($listing && (int) $listing->provider_id === (int) $authorId) {
            $redirect = $request->boolean('redirect_to_listing')
                ? redirect()->route('listings.details', $validated['listing_id'])
                : back();

            return $redirect->with('error', 'Nu poți lăsa o recenzie propriului tău anunț.');
        }
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

    public function update(Request $request, int|string $id)
    {
        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $review = $this->reviewService->updateReview($id, $validated, auth()->id());

        if (!$review) {
            abort(403, 'Nu ai permisiunea să modifici această recenzie.');
        }

        return redirect()->route('listings.details', $review->listing_id)
            ->with('success', 'Recenzia a fost actualizată!');
    }

    public function destroy(int|string $id)
    {
        $listingId = $this->reviewService->deleteOwnReview($id, auth()->id());

        if (!$listingId) {
            abort(403, 'Nu ai permisiunea să ștergi această recenzie.');
        }

        return redirect()->route('listings.details', $listingId)
            ->with('success', 'Recenzia a fost ștearsă.');
    }
}
