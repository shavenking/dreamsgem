<?php

namespace App\Events;

use App\Dragon;
use App\Operatable;
use App\OperationHistory;
use App\User;
use Illuminate\Broadcasting\PrivateChannel;

class DragonActivated implements ShouldCreateOperationHistory
{
    public $dragon;

    public $operator;

    /**
     * Create a new event instance.
     *
     * @param Dragon $dragon
     * @param User $operator
     */
    public function __construct(Dragon $dragon, User $operator)
    {
        $this->dragon = $dragon;
        $this->operator = $operator;
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
        return OperationHistory::TYPE_ACTIVATE;
    }
}
