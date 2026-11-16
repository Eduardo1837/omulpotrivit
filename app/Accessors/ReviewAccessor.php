<?php

namespace App\Accessors;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReviewAccessor
{
    /**
     * Returnează recenziile vizibile (is_hidden = false) pentru anunțuri active, paginate.
     */
    public function getVisiblePaginated(int $perPage = 10): LengthAwarePaginator
    {
        return Review::with(['listing', 'author'])
            ->where('is_hidden', false)
            ->whereHas('listing', fn ($q) => $q->where('status', 'activ'))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Returnează recenziile pentru o listare (implicit doar cele vizibile).
     */
    public function getByListingId(int|string $listingId, bool $visibleOnly = true): Collection
    {
        $query = Review::with('author')
            ->where('listing_id', $listingId)
            ->latest();

        if ($visibleOnly) {
            $query->where('is_hidden', false);
        }

        return $query->get();
    }

    /**
     * Găsește o recenzie după ID.
     */
    public function getById(int|string $id): ?Review
    {
        return Review::find($id);
    }

    /**
     * Creează o recenzie nouă.
     */
    public function create(array $data): Review
    {
        return Review::create($data);
    }

    /**
     * Setează starea de ascundere logică (is_hidden) pentru o recenzie.
     */
    public function setHidden(int|string $id, bool $hidden): bool
    {
        $review = Review::find($id);
        return $review ? $review->update(['is_hidden' => $hidden]) : false;
    }

    /**
     * Comută vizibilitatea (is_hidden) unei recenzii.
     */
    public function toggleHidden(int|string $id): ?Review
    {
        $review = Review::find($id);
        if (!$review) {
            return null;
        }

        $review->is_hidden = !$review->is_hidden;
        $review->save();

        return $review;
    }

    /**
     * Șterge fizic o recenzie după ID.
     */
    public function delete(int|string $id): bool
    {
        return (bool) Review::destroy($id);
    }
}
