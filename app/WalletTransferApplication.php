<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WalletTransferApplication extends Model
{
    const STATUS_PENDING = 0;

    protected $fillable = ['from_wallet_id', 'to_wallet_id', 'status', 'rate', 'amount', 'remark'];
}
