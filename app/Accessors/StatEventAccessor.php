<?php

namespace App\Accessors;

use App\Models\StatEvent;

class StatEventAccessor
{
    /**
     * Înregistrează un eveniment de statistică / analytics în baza de date.
     */
    public function create(array $data): StatEvent
    {
        return StatEvent::create($data);
    }
}
