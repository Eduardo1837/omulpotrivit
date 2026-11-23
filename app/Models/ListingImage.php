<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingImage extends Model
{
    protected $fillable = [
        'listing_id',
        'path',
        'sort_order',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function getUrlAttribute(): string
    {
        return '/storage/' . ltrim($this->path, '/');
    }
}
