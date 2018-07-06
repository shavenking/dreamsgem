<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CardApplication extends Model
{
    const STATUS_PENDING = 0;

    protected $fillable = ['user_id', 'nickname', 'address', 'phone', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
