<?php

namespace App\Accessors;

use App\Models\AppNotification;
use Illuminate\Database\Eloquent\Collection;

class AppNotificationAccessor
{
    /**
     * Creează o notificare nouă pentru un utilizator.
     */
    public function create(array $data): AppNotification
    {
        return AppNotification::create($data);
    }

    /**
     * Returnează notificările unui utilizator, ordonate descrescător după data creării.
     */
    public function getByUserId(int|string $userId): Collection
    {
        return AppNotification::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Marchează o notificare a utilizatorului ca citită.
     */
    public function markAsRead(int|string $id, int|string $userId): bool
    {
        $notification = AppNotification::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return false;
        }

        return $notification->update(['isRead' => true]);
    }

    /**
     * Marchează toate notificările necitite ale utilizatorului ca citite.
     */
    public function markAllAsRead(int|string $userId): int
    {
        return AppNotification::where('user_id', $userId)
            ->where('isRead', false)
            ->update(['isRead' => true]);
    }
}
