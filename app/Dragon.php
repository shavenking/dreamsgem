<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Dragon extends Model implements Operatable
{
    use OperatableTrait;

    const MAX_ACTIVATE_DRAGON_AMOUNT = 1;

    protected $fillable = ['user_id', 'activated_at'];

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

    public function getActivatedAttribute()
    {
        return !is_null($this->user_id);
    }

    public function activateDragon(User $targetUser)
    {
        $maxActivateDragonAmount = self::MAX_ACTIVATE_DRAGON_AMOUNT;

        $affectedCount = Dragon::whereId($this->id)
            ->where('owner_id', '!=', null)
            ->where('user_id', null)
            ->whereRaw(
                "$maxActivateDragonAmount > (
                    SELECT count(*) FROM 
                        (SELECT * FROM dragons WHERE user_id = {$targetUser->id}) AS dragon_temp 
                    WHERE user_id = {$targetUser->id}
                )"
            )
            ->update(
                [
                    'user_id' => $targetUser->id,
                    'activated_at' => Carbon::now(),
                ]
            );

        return $affectedCount;
    }

    public function scopeAvailableForBuying(Builder $builder)
    {
        return $builder->whereNull('owner_id')->whereNull('user_id');
    }
}
