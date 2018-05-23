<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OperationHistory extends Model
{
    const TYPE_INITIAL = 0;
    const TYPE_UPDATE = 1;
    const TYPE_BUY = 2;
    const TYPE_ACTIVATE = 3;
    const TYPE_RECALL = 4;
    const TYPE_TRANSFER = 5;

    protected $fillable = ['operator_id', 'user_id', 'type', 'result_data'];

    protected $casts = [
        'result_data' => 'array',
    ];

    public function operatable(): MorphTo
    {
        return $this->morphTo();
    }
}
