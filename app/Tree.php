<?php

namespace App;

use App\Jobs\TreeSettlement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Tree extends Model implements Operatable
{
    use OperatableTrait;

    const TYPE_SMALL = 0;
    const TYPE_MEDIUM = 1;
    const TYPE_LARGE = 2;

    protected $fillable = ['type', 'owner_id', 'user_id', 'remain', 'capacity', 'progress', 'activated_at'];

    protected $appends = ['activated'];

    protected $dates = ['activated_at'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeTreeSettleCandidates(Builder $builder)
    {
        return $builder->whereNotNull('user_id')->where('remain', '>', 0)->orderBy('id');
    }

    public function getActivatedAttribute()
    {
        return !is_null($this->user_id);
    }

    public function treeCapacity($type)
    {
        return data_get([
            90, // TYPE_SMALL
            190, // TYPE_MEDIUM
            300, // TYPE_LARGE
        ], $type, 90);
    }

    public function types()
    {
        return [
            self::TYPE_SMALL,
            self::TYPE_MEDIUM,
            self::TYPE_LARGE,
        ];
    }

    public function typeIsGreaterThan($type)
    {
        return $this->type > $type;
    }

    /**
     * @param $originalAward
     * @see TreeSettlement
     */
    public function multiplyAward($originalAward)
    {
        return $originalAward * ($this->type + 1);
    }

    public function treeTypesGreaterOrEqualThan(int $latestTreeType)
    {
        return array_values(array_filter($this->types(), function ($candidate) use ($latestTreeType) {
            return $latestTreeType <= $candidate;
        }));
    }
}
