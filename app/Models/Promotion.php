<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'plan_id', 'user_id', 'listing_id', 'request_id', 
        'startsAt', 'endsAt', 'status'
    ];

    public function plan()
    {
        return $this->belongsTo(PromotionPlan::class, 'plan_id');
    }

    public function payment()
    {
        // O promovare are de obicei o plată confirmată, deși poate avea mai multe tentative
        return $this->hasMany(Payment::class, 'promotion_id');
    }
}
