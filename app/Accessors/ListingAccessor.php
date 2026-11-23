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
        return Listing::with(['category', 'provider'])->latest()->get();
    }

    /**
     * Returnează doar listările active, prioritizând anunțurile promovate.
     */
    public function getActiveAll(): Collection
    {
        return Listing::with('category')
            ->where('status', 'activ')
            ->orderByDesc('is_featured')
            ->latest()
            ->get();
    }

    /**
     * Returnează listările active pentru pagina principală, prioritizând anunțurile promovate.
     */
    public function getActiveHomeListings(int $limit = 6): Collection
    {
        return Listing::with('category')
            ->where('status', 'activ')
            ->orderByDesc('is_featured')
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Găsește o listare după ID împreună cu datele categoriei.
     */
    public function getById(int|string $id): ?Listing
    {
        return Listing::with('category')->find($id);
    }

    /**
     * Găsește o listare activă după ID împreună cu datele categoriei.
     */
    public function getActiveById(int|string $id): ?Listing
    {
        return Listing::with('category')
            ->where('status', 'activ')
            ->find($id);
    }

    /**
     * Filtrează listările după cheia străină category_id.
     */
    public function getByCategoryId(int|string $categoryId): Collection
    {
        return Listing::where('category_id', $categoryId)->get();
    }

    /**
     * Filtrează listările active după cheia străină category_id.
     */
    public function getActiveByCategoryId(int|string $categoryId): Collection
    {
        return Listing::with('category')
            ->where('status', 'activ')
            ->where('category_id', $categoryId)
            ->orderByDesc('is_featured')
            ->latest()
            ->get();
    }

    /**
     * Returnează listările unui utilizator (cu recenzii vizibile), excluzând cele șterse.
     */
    public function getByUserId(int|string $userId, bool $activeOnly = true): Collection
    {
        $query = Listing::with([
            'category',
            'reviews' => fn ($q) => $q->where('is_hidden', false),
        ])
            ->where('provider_id', $userId)
            ->where('status', '!=', 'deleted')
            ->latest();

        if ($activeOnly) {
            $query->where('status', 'activ');
        }

        return $query->get();
    }

    /**
     * Numără listările unui utilizator.
     */
    public function countByUserId(int|string $userId, bool $activeOnly = true): int
    {
        $query = Listing::where('provider_id', $userId);

        if ($activeOnly) {
            $query->where('status', 'activ');
        }

        return $query->count();
    }

    /**
     * Numără listările gratuite (is_featured = false) ale unui utilizator.
     */
    public function countFreeByUserId(int|string $userId, bool $activeOnly = true): int
    {
        $query = Listing::where('provider_id', $userId)
            ->where('is_featured', false);

        if ($activeOnly) {
            $query->where('status', 'activ');
        }

        return $query->count();
    }

    /**
     * Verifică dacă utilizatorul are cel puțin o listare activă promovată.
     */
    public function hasFeaturedByUserId(int|string $userId): bool
    {
        return Listing::where('provider_id', $userId)
            ->where('status', 'activ')
            ->where('is_featured', true)
            ->exists();
    }

    /**
     * Numără listările promovate (is_featured = true) ale unui utilizator.
     */
    public function countFeaturedByUserId(int|string $userId, bool $activeOnly = true): int
    {
        $query = Listing::where('provider_id', $userId)
            ->where('is_featured', true);

        if ($activeOnly) {
            $query->where('status', 'activ');
        }

        return $query->count();
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
     * Ștergere logică: marchează listarea ca inactivă (status = 'inactiv').
     */
    public function softDelete(int|string $id): bool
    {
        $listing = Listing::find($id);
        return $listing ? $listing->update(['status' => 'inactiv']) : false;
    }

    /**
     * Marchează listarea ca ștearsă (status = 'deleted') pentru păstrarea istoricului.
     */
    public function markAsDeleted(int|string $id): bool
    {
        $listing = Listing::find($id);
        return $listing ? $listing->update(['status' => 'deleted', 'is_featured' => false]) : false;
    }

    /**
     * Șterge o listare după ID.
     */
    public function delete(int|string $id): bool
    {
        return (bool) Listing::destroy($id);
    }

    /**
     * Returnează numărul total de listări.
     */
    public function countAll(): int
    {
        return Listing::count();
    }

    /**
     * Salvare entitate nouă (echivalent AddEntity din cerință)
     */
    public function addEntity(array $data): Listing
    {
        return $this->create($data);
    }

    /**
     * Actualizare entitate existentă (echivalent UpdateEntity din cerință)
     */
    public function updateEntity(int|string $id, array $data): bool
    {
        return $this->update($id, $data);
    }
}