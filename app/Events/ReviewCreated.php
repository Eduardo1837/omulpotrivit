<?php

namespace App\Events;

use App\Models\Review;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Creează o instanță nouă a evenimentului pentru o recenzie nouă.
     */
    public function __construct(
        public Review $review,
    ) {}
}
