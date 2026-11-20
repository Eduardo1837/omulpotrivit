<?php

namespace App\Listeners;

use App\Accessors\StatEventAccessor;
use App\Events\ListingPromoted;
use Illuminate\Support\Facades\Log;

class RecordPromotionStatEvent
{
    /**
     * Injectează accessor-ul pentru evenimente de statistică.
     */
    public function __construct(
        private StatEventAccessor $statEventAccessor,
    ) {}

    /**
     * Înregistrează un eveniment de analytics în stat_events și scrie în log.
     */
    public function handle(ListingPromoted $event): void
    {
        $statEvent = $this->statEventAccessor->create([
            'viewer_id' => $event->userId,
            'listing_id' => $event->listing->id,
            'eventType' => 'promovare',
        ]);

        Log::info('Promotion stat event recorded', [
            'stat_event_id' => $statEvent->id,
            'listing_id' => $event->listing->id,
            'user_id' => $event->userId,
            'plan_id' => $event->plan?->id,
        ]);
    }
}
