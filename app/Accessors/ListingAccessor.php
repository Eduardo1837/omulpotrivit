<?php

namespace App\Accessors;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;

class ListingAccessor
{
    /**
     * Returnează toate listările împreună cu categoria din care fac parte.
     */
    public function getAll(): Collection
    {
        return Listing::with('category')->get();
    }

    /**
     * Găsește o listare după ID împreună cu datele categoriei.
     */
    public function getById(int|string $id): ?Listing
    {
        return Listing::with('category')->find($id);
    }

    /**
     * Filtrează listările după cheia străină category_id.
     */
    public function getByCategoryId(int|string $categoryId): Collection
    {
        return Listing::where('category_id', $categoryId)->get();
    }

    /**
     * Creează o listare nouă asociată unei categorii.
     */
    public function create(array $data): Listing
    {
        return Listing::create($data);
    }

    /**
     * Actualizează o listare existentă.
     */
    public function update(int|string $id, array $data): bool
    {
        $listing = Listing::find($id);
        return $listing ? $listing->update($data) : false;
    }

    /**
     * Șterge o listare după ID.
     */
    public function delete(int|string $id): bool
    {
        return (bool) Listing::destroy($id);
    }
}