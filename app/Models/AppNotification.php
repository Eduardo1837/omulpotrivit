<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    // Specificăm explicit numele tabelului
    protected $table = 'notifications';

    protected $fillable = [
        'user_id', 'kind', 'message', 'isRead'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
