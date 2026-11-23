<?php

namespace App\Listeners;

use App\Accessors\AppNotificationAccessor;
use App\Events\ListingPromoted;
use App\Events\NotificationCreated;

class SendPromotionNotification
{
    /**
     * Injectează accessor-ul pentru notificări.
     */
    public function __construct(
        private AppNotificationAccessor $notificationAccessor,
    ) {}

    /**
     * Salvează o notificare în aplicație atunci când un anunț este promovat.
     */
    public function handle(ListingPromoted $event): void
    {
        $planLabel = $event->plan ? " cu planul {$event->plan->name}" : '';

        $notification = $this->notificationAccessor->create([
            'user_id' => $event->userId,
            'kind' => 'listing_promoted',
            'message' => "Anunțul \"{$event->listing->title}\" a fost promovat{$planLabel}.",
            'isRead' => false,
        ]);

        NotificationCreated::dispatch($notification);
    }
}
