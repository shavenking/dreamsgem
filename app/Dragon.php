<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dragon extends Model implements Operatable
{
    use OperatableTrait;

    protected $fillable = ['user_id'];

    public function getActivatedAttribute()
    {
        return !is_null($this->user_id);
    }
}
