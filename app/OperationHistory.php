<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OperationHistory extends Model
{
    const TYPE_INITIAL = 0;
    const TYPE_UPDATE = 1;
    const TYPE_ACTIVATE = 2;
    const TYPE_RECALL = 3;
    const TYPE_TRANSFER = 4;
    const TYPE_TRANSFORM = 5;

    const SUB_TYPE_AWARD_UPLINE = 0; // 推薦獎勵
    const SUB_TYPE_AWARD_ACTIVATE_DRAGON = 1; // 激活龍獎勵
    const SUB_TYPE_AWARD_ACTIVATE_TREE = 2; // 激活樹獎勵
    const SUB_TYPE_AWARD_SETTLEMENT = 3; // 結算獎勵
    const SUB_TYPE_BUY_DRAGON = 4; // 購買龍
    const SUB_TYPE_BUY_TREE = 5; // 購買樹
    const SUB_TYPE_WITHHELD = 6; // 錢包轉換預扣
    const SUB_TYPE_EMAIL_VERIFIED = 7; // 信箱驗證
    const SUB_TYPE_UPDATE_EXTERNAL_ADDRESS = 8;
    const SUB_TYPE_AWARD_BUY_TREE = 9; // 購買樹獎勵
    const SUB_TYPE_BUY_TREE_SETTLEMENT = 10; // 購買樹獎勵結算

    protected $fillable = ['operator_id', 'user_id', 'type', 'result_data', 'delta', 'sub_type'];

    protected $casts = [
        'result_data' => 'array',
        'delta' => 'array',
    ];

    public function operatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toArray()
    {
        $data = parent::toArray();

        $data['operatable_type'] = $this->transformOperatableType($data['operatable_type']);

        return $data;
    }

    public function transformOperatableType($originalOperatableType)
    {
        return data_get([
            'App\User' => 0,
            'App\Wallet' => 1,
            'App\Dragon' => 2,
            'App\Tree' => 3,
            'App\WalletTransferApplication' => 4,
        ], $originalOperatableType, 99);
    }

    public function reverseTransformOperatableType(int $originalOperatableType)
    {
        return data_get([
            'App\User',
            'App\Wallet',
            'App\Dragon',
            'App\Tree',
            'App\WalletTransferApplication',
        ], $originalOperatableType);
    }

    public function scopeReverseOperatableType(Builder $query, $operatableType)
    {
        if (is_array($operatableType)) {
            $operatableType = array_map([$this, 'reverseTransformOperatableType'], $operatableType);

            return $query->whereIn('operatable_type', $operatableType);
        }

        return $query->where('operatable_type', $this->reverseTransformOperatableType($operatableType));
    }
}
