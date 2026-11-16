<?php

namespace App\Services;

use App\Accessors\UserAccessor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        protected UserAccessor $userAccessor
    ) {
    }

    /**
     * Returnează toți utilizatorii cu listările lor pentru panoul de administrare.
     */
    public function getAllUsersWithListings(): Collection
    {
        return $this->userAccessor->getAllWithListings();
    }

    /**
     * Returnează toți utilizatorii (ex. pentru selectoare/dropdown-uri).
     */
    public function getAllUsers(): Collection
    {
        return $this->userAccessor->getAll();
    }

    /**
     * Găsește un utilizator după ID.
     */
    public function getUserById(int|string $id): ?User
    {
        return $this->userAccessor->getById($id);
    }

    /**
     * Returnează numărul total de utilizatori.
     */
    public function countUsers(): int
    {
        return $this->userAccessor->count();
    }

    /**
     * Creează un utilizator nou din panoul de administrare.
     */
    public function createUser(array $data, ?int $actingUserId = null): User
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->userAccessor->create($data);

        Log::info('Admin created user', [
            'created_user_id' => $user->id,
            'role' => $user->role,
            'acting_user_id' => $actingUserId,
        ]);

        return $user;
    }

    /**
     * Actualizează un utilizator existent.
     */
    public function updateUser(int|string $id, array $data, ?int $actingUserId = null): bool
    {
        if (array_key_exists('password', $data)) {
            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }
        }

        $updated = $this->userAccessor->update($id, $data);

        if ($updated) {
            Log::info('Admin updated user', [
                'target_user_id' => $id,
                'acting_user_id' => $actingUserId,
            ]);
        } else {
            Log::warning('Admin user update failed: user not found', [
                'target_user_id' => $id,
                'acting_user_id' => $actingUserId,
            ]);
        }

        return $updated;
    }

    /**
     * Șterge un utilizator (interzice auto-ștergerea).
     */
    public function deleteUser(int|string $id, ?int $actingUserId = null): bool
    {
        if ($actingUserId !== null && (int) $id === $actingUserId) {
            Log::warning('Admin attempted self-deletion', [
                'acting_user_id' => $actingUserId,
            ]);
            return false;
        }

        $deleted = $this->userAccessor->delete($id);

        if ($deleted) {
            Log::warning('Admin deleted user', [
                'target_user_id' => $id,
                'acting_user_id' => $actingUserId,
            ]);
        }

        return $deleted;
    }
}
