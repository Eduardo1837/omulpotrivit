<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ListingService;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewService $reviewService,
        protected ListingService $listingService
    ) {
    }

    /**
     * GET /api/reviews
     * Returnează recenziile publice vizibile în format JSON
     */
    public function index(): JsonResponse
    {
        $reviews = $this->reviewService->getVisibleReviewsPaginated(10);

        $items = collect($reviews->items())->map(fn ($review) => [
            'id' => $review->id,
            'listing_id' => $review->listing_id,
            'listing_title' => $review->listing->title ?? ('Listing #' . $review->listing_id),
            'author_id' => $review->author_id,
            'author_name' => $review->author->name ?? 'Utilizator',
            'rating' => (int) $review->rating,
            'comment' => $review->comment,
            'created_at_human' => $review->created_at?->diffForHumans(),
        ])->values();

        return response()->json([
            'reviews' => $items,
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
            ],
        ], 200);
    }

    /**
     * POST /api/listings/{id}/reviews
     * Adaugă o recenzie nouă pentru un anunț activ
     */
    public function store(Request $request, int|string $id): JsonResponse
    {
        $authorId = auth()->id();
        if (!$authorId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        $listing = $this->listingService->getActiveListingById($id);
        if (!$listing) {
            return response()->json(['message' => 'Anunțul nu a fost găsit.'], 404);
        }

        if ((int) $listing->provider_id === (int) $authorId) {
            return response()->json([
                'message' => 'Nu poți lăsa o recenzie propriului tău anunț.',
            ], 403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $existing = $this->reviewService->getReviewsForListing($id, visibleOnly: false)
            ->firstWhere('author_id', $authorId);

        if ($existing) {
            return response()->json([
                'message' => 'Ai lăsat deja o recenzie pentru acest anunț.',
            ], 422);
        }

        $review = $this->reviewService->createReview([
            'listing_id' => (int) $id,
            'rating' => (int) $validated['rating'],
            'comment' => $validated['comment'],
        ], $authorId);

        return response()->json([
            'message' => 'Recenzia a fost adăugată cu succes!',
            'review' => [
                'id' => $review->id,
                'listing_id' => $review->listing_id,
                'author_id' => $review->author_id,
                'author_name' => $request->user()?->name ?? 'Utilizator',
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'created_at_human' => $review->created_at?->diffForHumans(),
            ],
        ], 201);
    }

    /**
     * PUT /api/reviews/{id}
     * Actualizează recenzia proprie a utilizatorului autentificat
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $review = $this->reviewService->updateReview($id, $validated, (int) $userId);
        if (!$review) {
            return response()->json(['message' => 'Nu ai permisiunea să modifici această recenzie.'], 403);
        }

        return response()->json([
            'message' => 'Recenzia a fost actualizată!',
            'review' => [
                'id' => $review->id,
                'listing_id' => $review->listing_id,
                'author_id' => $review->author_id,
                'author_name' => $request->user()?->name ?? 'Utilizator',
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'created_at_human' => $review->created_at?->diffForHumans(),
            ],
        ], 200);
    }

    /**
     * DELETE /api/reviews/{id}
     * Șterge recenzia proprie a utilizatorului autentificat
     */
    public function destroy(int|string $id): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        $listingId = $this->reviewService->deleteOwnReview($id, (int) $userId);
        if (!$listingId) {
            return response()->json(['message' => 'Nu ai permisiunea să ștergi această recenzie.'], 403);
        }

        return response()->json([
            'message' => 'Recenzia a fost ștearsă.',
            'listing_id' => $listingId,
        ], 200);
    }
}
