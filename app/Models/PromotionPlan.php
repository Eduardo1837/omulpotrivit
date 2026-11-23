<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionPlan extends Model
{
    protected $fillable = ['name', 'description', 'price', 'currency', 'durationDays', 'maxItems', 'is_hidden', 'is_deleted'];

    protected $casts = [
        'is_hidden' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'plan_id');
    }
}
