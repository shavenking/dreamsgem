<?php

namespace App\Events;

use App\Operatable;
use App\OperationHistory;
use App\Tree;
use App\User;
use Illuminate\Broadcasting\PrivateChannel;

class TreeUpdated implements ShouldCreateOperationHistory
{
    public $tree;

    public $operator;

    /**
     * Create a new event instance.
     *
     * @param Tree $tree
     * @param User|null $operator
     */
    public function __construct(Tree $tree, User $operator = null)
    {
        $this->tree = $tree;
        $this->operator = $operator;
    }

    public function getOperatable(): Operatable
    {
        return $this->tree;
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