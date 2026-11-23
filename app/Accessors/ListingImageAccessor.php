<?php

namespace App\Accessors;

use App\Models\ListingImage;
use Illuminate\Database\Eloquent\Collection;

class ListingImageAccessor
{
    /**
     * Returnează toate imaginile unei listări în ordinea sortării.
     */
    public function getByListingId(int|string $listingId): Collection
    {
        return ListingImage::where('listing_id', $listingId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Găsește o imagine după ID.
     */
    public function getById(int|string $id): ?ListingImage
    {
        return ListingImage::with('listing')->find($id);
    }

    /**
     * Salvează o listă de căi de imagini pentru o listare.
     */
    public function createMany(int|string $listingId, array $paths): void
    {
        $maxOrder = (int) (ListingImage::where('listing_id', $listingId)->max('sort_order') ?? -1);

        foreach ($paths as $index => $path) {
            ListingImage::create([
                'listing_id' => $listingId,
                'path' => $path,
                'sort_order' => $maxOrder + 1 + $index,
            ]);
        }
    }

    /**
     * Șterge o imagine după ID.
     */
    public function delete(int|string $id): bool
    {
        return (bool) ListingImage::destroy($id);
    }
}
