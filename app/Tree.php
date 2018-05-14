<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tree extends Model implements Operatable
{
    use OperatableTrait;

    protected $fillable = ['owner_id', 'user_id', 'remain', 'capacity', 'progress'];

    protected $appends = ['activated'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivatedAttribute()
    {
        return !is_null($this->user_id);
    }
}
