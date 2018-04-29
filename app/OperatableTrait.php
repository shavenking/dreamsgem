<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait OperatableTrait
{
    public function operationHistories(): MorphMany
    {
        return $this->morphMany(OperationHistory::class, 'operatable');
    }
}
