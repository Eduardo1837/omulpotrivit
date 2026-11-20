<?php

namespace App\Services;

use App\Accessors\CategoryAccessor;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    public const CACHE_KEY_ALL_CATEGORIES = 'categories.all';
    public const CACHE_KEY_ACTIVE_CATEGORIES = 'categories.active';
    public const CACHE_TTL_SECONDS = 300;

    public function __construct(
        protected CategoryAccessor $categoryAccessor
    ) {
    }

    /**
     * Returnează toate categoriile (cu listări eager-loaded), din cache.
     */
    public function getAllCategories(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ALL_CATEGORIES, self::CACHE_TTL_SECONDS, function () {
            return $this->categoryAccessor->getAll();
        });
    }

    /**
     * Returnează categoriile cu listările active (status = 'activ'), din cache.
     */
    public function getAllCategoriesWithActiveListings(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ACTIVE_CATEGORIES, self::CACHE_TTL_SECONDS, function () {
            return $this->categoryAccessor->getAllWithActiveListings();
        });
    }

    /**
     * Găsește o categorie după ID.
     */
    public function getCategoryById(int|string $id): ?Category
    {
        return $this->categoryAccessor->getById($id);
    }

    /**
     * Creează o categorie nouă și invalidează cache-ul.
     */
    public function createCategory(array $data): Category
    {
        $category = $this->categoryAccessor->create($data);
        $this->invalidateCategoriesCache();

        Log::info('Category created', [
            'category_id' => $category->id,
            'name' => $category->name,
        ]);

        return $category;
    }

    /**
     * Actualizează o categorie și invalidează cache-ul.
     */
    public function updateCategory(int|string $id, array $data): bool
    {
        $updated = $this->categoryAccessor->update($id, $data);

        if ($updated) {
            $this->invalidateCategoriesCache();
            Log::info('Category updated', ['category_id' => $id]);
        }

        return $updated;
    }

    /**
     * Șterge o categorie și invalidează cache-ul.
     */
    public function deleteCategory(int|string $id): bool
    {
        $deleted = $this->categoryAccessor->delete($id);

        if ($deleted) {
            $this->invalidateCategoriesCache();
            Log::info('Category deleted', ['category_id' => $id]);
        }

        return $deleted;
    }

    /**
     * Invalidează cheile de cache pentru categorii.
     */
    public function invalidateCategoriesCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL_CATEGORIES);
        Cache::forget(self::CACHE_KEY_ACTIVE_CATEGORIES);
    }
}
