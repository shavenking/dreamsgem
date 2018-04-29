<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OperationHistory extends Model
{
    const TYPE_INITIAL = 0;

    protected $fillable = ['user_id', 'type', 'result_data'];

    protected $casts = [
        'result_data' => 'array',
    ];

    public function operatable(): MorphTo
    {
        return $this->morphTo();
    }
}
