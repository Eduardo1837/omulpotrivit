<?php

namespace App\Accessors;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryAccessor
{
    /**
     * Returnează toate categoriile împreună cu listările asociate (Eager Loading).
     */
    public function getAll(): Collection
    {
        return Category::with('listings')->get();
    }

    /**
     * Găsește o categorie după ID împreună cu listările asociate.
     */
    public function getById(int|string $id): ?Category
    {
        return Category::with('listings')->find($id);
    }

    /**
     * Adaugă o nouă categorie în baza de date.
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Actualizează datele unei categorii.
     */
    public function update(int|string $id, array $data): bool
    {
        $category = Category::find($id);
        return $category ? $category->update($data) : false;
    }

    /**
     * Șterge o categorie după ID.
     */
    public function delete(int|string $id): bool
    {
        return (bool) Category::destroy($id);
    }
}