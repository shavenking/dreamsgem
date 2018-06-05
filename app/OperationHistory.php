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

    const SUB_TYPE_AWARD_UPLINE = 0; // 推薦獎勵
    const SUB_TYPE_AWARD_ACTIVATE_DRAGON = 1; // 激活龍獎勵
    const SUB_TYPE_AWARD_ACTIVATE_TREE = 2; // 激活樹獎勵
    const SUB_TYPE_AWARD_SETTLEMENT = 3; // 結算獎勵

    protected $fillable = ['operator_id', 'user_id', 'type', 'result_data', 'delta', 'sub_type'];

    protected $casts = [
        'result_data' => 'array',
        'delta' => 'array',
    ];

    public function operatable(): MorphTo
    {
        return $this->morphTo();
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
        ], $originalOperatableType, 99);
    }

    public function reverseTransformOperatableType(int $originalOperatableType)
    {
        return data_get([
            'App\User',
            'App\Wallet',
            'App\Dragon',
            'App\Tree',
        ], $originalOperatableType);
    }

    public function scopeReverseOperatableType(Builder $query, int $operatableType)
    {
        return $query->where('operatable_type', $this->reverseTransformOperatableType($operatableType));
    }
}
