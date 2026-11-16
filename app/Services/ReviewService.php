<?php

namespace App\Services;

use App\Accessors\ReviewAccessor;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ReviewService
{
    public function __construct(
        protected ReviewAccessor $reviewAccessor
    ) {
    }

    /**
     * Returnează recenziile publice vizibile (is_hidden = false) pentru anunțuri active.
     */
    public function getVisibleReviewsPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->reviewAccessor->getVisiblePaginated($perPage);
    }

    /**
     * Returnează recenziile unei listări (implicit doar cele vizibile).
     */
    public function getReviewsForListing(int|string $listingId, bool $visibleOnly = true): Collection
    {
        return $this->reviewAccessor->getByListingId($listingId, $visibleOnly);
    }

    /**
     * Creează o recenzie nouă pentru un autor dat.
     */
    public function createReview(array $data, int $authorId): Review
    {
        $data['author_id'] = $authorId;
        $data['is_hidden'] = $data['is_hidden'] ?? false;

        $review = $this->reviewAccessor->create($data);

        Log::info('Review created', [
            'review_id' => $review->id,
            'listing_id' => $review->listing_id,
            'author_id' => $authorId,
        ]);

        return $review;
    }

    /**
     * Comută vizibilitatea (is_hidden) unei recenzii.
     */
    public function toggleVisibility(int|string $id, ?int $actingUserId = null): ?Review
    {
        $review = $this->reviewAccessor->toggleHidden($id);

        if ($review) {
            Log::info('Review visibility toggled', [
                'review_id' => $review->id,
                'is_hidden' => $review->is_hidden,
                'acting_user_id' => $actingUserId,
            ]);
        }

        return $review;
    }

    /**
     * Ștergere logică a unei recenzii (is_hidden = true).
     */
    public function hideReview(int|string $id, ?int $actingUserId = null): bool
    {
        $hidden = $this->reviewAccessor->setHidden($id, true);

        if ($hidden) {
            Log::info('Review logically hidden (is_hidden = true)', [
                'review_id' => $id,
                'acting_user_id' => $actingUserId,
            ]);
        } else {
            Log::warning('Review logical hiding failed: review not found', [
                'review_id' => $id,
                'acting_user_id' => $actingUserId,
            ]);
        }

        return $hidden;
    }
}
