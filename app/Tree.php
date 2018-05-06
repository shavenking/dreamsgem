<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tree extends Model implements Operatable
{
    use OperatableTrait;

    protected $fillable = ['owner_id', 'user_id', 'remain', 'capacity', 'progress'];

    public function getActivatedAttribute()
    {
        return !is_null($this->user_id);
    }
}
