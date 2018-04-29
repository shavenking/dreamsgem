<?php

namespace App\Events;

use App\Operatable;
use App\OperationHistory;
use App\User;

class UserCreated implements ShouldCreateOperationHistory
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getOperatable(): Operatable
    {
        return $this->user;
    }

    public function getOperator(): ?User
    {
        return null;
    }

    public function getType(): int
    {
        return OperationHistory::TYPE_INITIAL;
    }
}
