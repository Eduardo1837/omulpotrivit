<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatEvent extends Model
{
    protected $fillable = [
        'viewer_id', 'listing_id', 'request_id', 
        'eventType', 'searchQuery', 'resultPosition'
    ];

    public function viewer()
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }
}
