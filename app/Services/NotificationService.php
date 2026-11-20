<?php

namespace App\Services;

use App\Accessors\AppNotificationAccessor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        protected AppNotificationAccessor $notificationAccessor,
    ) {}

    /**
     * Returnează toate notificările utilizatorului.
     */
    public function getUserNotifications(int|string $userId): Collection
    {
        return $this->notificationAccessor->getByUserId($userId);
    }

    /**
     * Marchează o notificare ca citită pentru utilizatorul autentificat.
     */
    public function markAsRead(int|string $id, int|string $userId): bool
    {
        $updated = $this->notificationAccessor->markAsRead($id, $userId);

        if ($updated) {
            Log::info('Notification marked as read', [
                'notification_id' => $id,
                'user_id' => $userId,
            ]);
        }

        return $updated;
    }

    /**
     * Marchează toate notificările utilizatorului ca citite.
     */
    public function markAllAsRead(int|string $userId): int
    {
        $count = $this->notificationAccessor->markAllAsRead($userId);

        if ($count > 0) {
            Log::info('All notifications marked as read', [
                'user_id' => $userId,
                'updated_count' => $count,
            ]);
        }

        return $count;
    }
}
