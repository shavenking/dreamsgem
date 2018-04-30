<?php

namespace App\Events;

use App\Operatable;
use App\OperationHistory;
use App\User;
use Illuminate\Broadcasting\PrivateChannel;

class UserUpdated implements ShouldCreateOperationHistory
{
    /**
     * @var User
     */
    public $user;
    /**
     * @var User
     */
    public $operator;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param User $operator
     */
    public function __construct(User $user, User $operator = null)
    {
        $this->user = $user;
        $this->operator = $operator;
    }

    public function getOperatable(): Operatable
    {
        return $this->user;
    }

    public function getOperator(): ?User
    {
        return $this->operator;
    }

    public function getType(): int
    {
        return OperationHistory::TYPE_UPDATE;
    }
}
