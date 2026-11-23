<?php

namespace App\Listeners;

use App\Accessors\AppNotificationAccessor;
use App\Events\ReviewCreated;

class SendReviewNotification
{
    /**
     * Injectează accessor-ul pentru notificări.
     */
    public function __construct(
        private AppNotificationAccessor $notificationAccessor,
    ) {}

    /**
     * Trimite o notificare proprietarului anunțului atunci când primește o recenzie.
     */
    public function handle(ReviewCreated $event): void
    {
        $review = $event->review->loadMissing(['listing', 'author']);
        $listing = $review->listing;

        if (!$listing || (int) $listing->provider_id === (int) $review->author_id) {
            return;
        }

        $authorName = $review->author?->name ?? 'Un utilizator';

        $this->notificationAccessor->create([
            'user_id' => $listing->provider_id,
            'kind' => 'review_received',
            'message' => "{$authorName} a lăsat o recenzie ({$review->rating}★) la anunțul \"{$listing->title}\".",
            'isRead' => false,
        ]);
    }
}
