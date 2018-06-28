<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WalletTransferApplication extends Model implements Operatable
{
    use OperatableTrait;

    const STATUS_PENDING = 0;

    protected $fillable = ['from_wallet_id', 'to_wallet_id', 'status', 'rate', 'amount', 'remark'];

    public function fromWallet()
    {
        return $this->belongsTo(Wallet::class, 'from_wallet_id');
    }

    public function toWallet()
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }
}
