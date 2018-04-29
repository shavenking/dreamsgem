<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tree extends Model implements Operatable
{
    use OperatableTrait;

    protected $fillable = ['user_id', 'remain', 'capacity', 'progress'];
}
