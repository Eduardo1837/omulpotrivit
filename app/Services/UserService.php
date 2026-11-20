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
     * Returnează utilizatorii non-admin eligibili ca furnizori.
     */
    public function getAllProviders(): Collection
    {
        return $this->userAccessor->getAllProviders();
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

    /**
     * Înregistrează un utilizator nou prin API.
     */
    public function registerUser(array $data): User
    {
        $user = $this->userAccessor->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'city' => $data['city'] ?? null,
            'phone' => $data['phone'] ?? null,
            'role' => 'user',
        ]);

        Log::info('User registered via API', ['user_id' => $user->id]);

        return $user;
    }

    /**
     * Autentifică un utilizator după email și parolă pentru API.
     */
    public function authenticateUser(string $email, string $password): ?User
    {
        $user = $this->userAccessor->getByEmail($email);
        if (!$user || !Hash::check($password, $user->password)) {
            Log::warning('Failed API login attempt', ['email' => $email]);
            return null;
        }

        Log::info('User authenticated via API', ['user_id' => $user->id]);

        return $user;
    }
}
