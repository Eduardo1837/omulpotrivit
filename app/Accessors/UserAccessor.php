<?php

namespace App\Accessors;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserAccessor
{
    /**
     * Returnează toți utilizatorii împreună cu listările asociate (Eager Loading).
     */
    public function getAllWithListings(): Collection
    {
        return User::with('listings')->get();
    }

    /**
     * Returnează toți utilizatorii (ordonați alfabetic după nume).
     */
    public function getAll(): Collection
    {
        return User::orderBy('name')->get();
    }

    /**
     * Returnează utilizatorii non-admin (furnizori eligibili), ordonați după nume.
     */
    public function getAllProviders(): Collection
    {
        return User::where('role', '!=', 'admin')->orderBy('name')->get();
    }

    /**
     * Găsește un utilizator după ID.
     */
    public function getById(int|string $id): ?User
    {
        return User::find($id);
    }

    /**
     * Găsește un utilizator după adresă de email.
     */
    public function getByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Returnează numărul total de utilizatori.
     */
    public function count(): int
    {
        return User::count();
    }

    /**
     * Creează un utilizator nou.
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Actualizează datele unui utilizator existent.
     */
    public function update(int|string $id, array $data): bool
    {
        $user = User::find($id);
        return $user ? $user->update($data) : false;
    }

    /**
     * Șterge un utilizator după ID.
     */
    public function delete(int|string $id): bool
    {
        return (bool) User::destroy($id);
    }
}
