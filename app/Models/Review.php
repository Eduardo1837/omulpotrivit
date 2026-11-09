<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['listing_id', 'author_id', 'rating', 'comment', 'is_hidden'];

    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
