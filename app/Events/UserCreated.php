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
        $this->user = $user->refresh();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getDelta(): ?array
    {
        return null;
    }
}
