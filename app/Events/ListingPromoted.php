<?php

namespace App\Events;

use App\Models\Listing;
use App\Models\PromotionPlan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ListingPromoted
{
    use Dispatchable, SerializesModels;

    /**
     * Creează o instanță nouă a evenimentului de promovare a unui anunț.
     */
    public function __construct(
        public Listing $listing,
        public int $userId,
        public ?PromotionPlan $plan = null,
    ) {}
}
