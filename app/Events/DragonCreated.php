<?php

namespace App\Events;

use App\Dragon;
use App\Operatable;
use App\OperationHistory;
use App\User;

class DragonCreated implements ShouldCreateOperationHistory
{
    public $dragon;

    public $operator;

    /**
     * Create a new event instance.
     *
     * @param Dragon $dragon
     * @param User $operator
     */
    public function __construct(Dragon $dragon, User $operator = null)
    {
        $this->dragon = $dragon->refresh();
        $this->operator = optional($operator)->refresh();
    }

    public function getOperatable(): Operatable
    {
        return $this->dragon;
    }

    public function getOperator(): ?User
    {
        return $this->operator;
    }

    public function getType(): int
    {
        return OperationHistory::TYPE_INITIAL;
    }

    public function getUser(): ?User
    {
        return $this->dragon->owner;
    }

    public function getDelta(): ?array
    {
        return null;
    }
}
