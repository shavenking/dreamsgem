<?php

namespace App\Events;

use App\Operatable;
use App\OperationHistory;
use App\Tree;
use App\User;
use Illuminate\Broadcasting\PrivateChannel;

class TreeActivated implements ShouldCreateOperationHistory
{
    public $tree;

    public $operator;

    /**
     * Create a new event instance.
     *
     * @param Tree $tree
     * @param User $operator
     */
    public function __construct(Tree $tree, User $operator = null)
    {
        $this->tree = $tree->refresh();
        $this->operator = optional($operator)->refresh();
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
        return OperationHistory::TYPE_ACTIVATE;
    }

    public function getUser(): ?User
    {
        return $this->tree->user;
    }

    public function getDelta(): ?array
    {
        return null;
    }
}
