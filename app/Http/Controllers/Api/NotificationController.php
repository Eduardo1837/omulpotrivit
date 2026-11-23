<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    /**
     * Returnează notificările utilizatorului autentificat.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notificationService->getUserNotifications($request->user()->id);
        $unreadCount = $notifications->where('isRead', false)->count();

        return response()->json([
            'notifications' => $notifications->values(),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Marchează o notificare specifică ca citită.
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $updated = $this->notificationService->markAsRead($id, $request->user()->id);

        if (!$updated) {
            return response()->json(['message' => 'Notificarea nu a fost găsită.'], 404);
        }

        return response()->json(['message' => 'Notificarea a fost marcată ca citită.']);
    }

    /**
     * Marchează toate notificările utilizatorului ca citite.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $updatedCount = $this->notificationService->markAllAsRead($request->user()->id);

        return response()->json([
            'message' => 'Toate notificările au fost marcate ca citite.',
            'updated_count' => $updatedCount,
        ]);
    }

    /**
     * Șterge toate notificările utilizatorului autentificat.
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $deletedCount = $this->notificationService->deleteAllForUser($request->user()->id);

        return response()->json([
            'message' => 'Toate notificările au fost șterse.',
            'deleted_count' => $deletedCount,
        ]);
    }
}
