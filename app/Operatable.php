<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Operatable
{
    public function operationHistories(): MorphMany;

    public function getAttributes();
}
