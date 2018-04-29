<?php

namespace App\Events;

use App\Operatable;
use App\User;

interface ShouldCreateOperationHistory
{
    public function getOperatable(): Operatable;

    public function getOperator(): ?User;

    public function getType(): int;
}
