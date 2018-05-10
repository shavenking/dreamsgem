<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SettlementHistory extends Model
{
    public function treeSettlementHistories()
    {
        return $this->hasMany(TreeSettlementHistory::class);
    }
}
