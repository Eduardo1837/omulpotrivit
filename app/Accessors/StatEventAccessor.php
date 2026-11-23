<?php

namespace App\Accessors;

use App\Models\StatEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StatEventAccessor
{
    /**
     * Înregistrează un eveniment de statistică / analytics în baza de date.
     */
    public function create(array $data): StatEvent
    {
        return StatEvent::create($data);
    }

    /**
     * Returnează numărul total de evenimente pentru un anunț după tip ('home_impression', 'click').
     */
    public function countByListingAndType(int|string $listingId, string $eventType): int
    {
        return StatEvent::where('listing_id', $listingId)
            ->where('eventType', $eventType)
            ->count();
    }

    /**
     * Returnează poziția medie pe pagina principală (1..6) pentru un anunț.
     */
    public function getAveragePosition(int|string $listingId, string $eventType = 'home_impression'): ?float
    {
        $avg = StatEvent::where('listing_id', $listingId)
            ->where('eventType', $eventType)
            ->whereNotNull('resultPosition')
            ->avg('resultPosition');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    /**
     * Returnează evenimentele din ultimele $days zile pentru un anunț.
     */
    public function getEventsForListingSince(int|string $listingId, Carbon $since): Collection
    {
        return StatEvent::where('listing_id', $listingId)
            ->where('created_at', '>=', $since)
            ->whereIn('eventType', ['home_impression', 'click'])
            ->get(['eventType', 'resultPosition', 'created_at']);
    }
}
