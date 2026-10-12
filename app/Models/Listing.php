<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'provider_id', 
        'category_id', 
        'title', 
        'description', 
        'price', 
        'priceType', 
        'city', 
        'imageUrl', 
        'status'
    ];

   
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'listing_skills');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'listing_id');
    }
}
