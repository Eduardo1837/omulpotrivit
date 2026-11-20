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
}
