<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $table = 'requests';

    protected $fillable = [
        'requester_id', 
        'category_id', 
        'title', 
        'description', 
        'budget', 
        'city', 
        'deadline', 
        'status'
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'request_id');
    }
}
