<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dragon extends Model implements Operatable
{
    use OperatableTrait;

    const MAX_ACTIVATE_DRAGON_AMOUNT = 1;

    protected $fillable = ['user_id'];

    protected $appends = ['activated'];

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
                ]
            );

        return $affectedCount;
    }

}
