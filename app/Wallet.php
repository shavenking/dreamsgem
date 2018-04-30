<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model implements Operatable
{
    use OperatableTrait;

    const GEM_QI_CAI = 0;
    const GEM_DUO_XI = 1;
    const GEM_DUO_FU = 2;
    const GEM_DUO_CAI = 3;

    protected $fillable = ['user_id', 'gem', 'amount'];
}
