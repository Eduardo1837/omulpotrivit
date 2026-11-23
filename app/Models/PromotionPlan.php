<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionPlan extends Model
{
    protected $fillable = ['name', 'price', 'currency', 'durationDays', 'maxItems', 'is_hidden'];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'plan_id');
    }
}
