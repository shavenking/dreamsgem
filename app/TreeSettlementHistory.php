<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TreeSettlementHistory extends Model
{
    const KEY_SETTLEMENT_DAILY = 'KEY_SETTLEMENT_DAILY';
    const KEY_SETTLEMENT_DOWNLINES = 'KEY_SETTLEMENT_DOWNLINES';
    const KEY_SETTLEMENT_AWARD = 'KEY_SETTLEMENT_AWARD';
    const KEY_SETTLEMENT_DOWNLINES_AFTER_TEN_LEVELS = 'KEY_SETTLEMENT_DOWNLINES_AFTER_TEN_LEVELS';

    protected $fillable = ['settlement_history_id', 'user_id', 'progress_gained', 'maximum_progress_rule'];

    protected $casts = [
        'progress_gained' => 'object',
        'maximum_progress_rule' => 'object',
    ];
}
