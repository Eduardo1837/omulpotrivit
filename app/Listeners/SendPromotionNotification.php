<?php

namespace App\Listeners;

use App\Accessors\AppNotificationAccessor;
use App\Events\ListingPromoted;

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

        $this->notificationAccessor->create([
            'user_id' => $event->userId,
            'kind' => 'listing_promoted',
            'message' => "Anunțul \"{$event->listing->title}\" a fost promovat{$planLabel}.",
            'isRead' => false,
        ]);
    }
}
