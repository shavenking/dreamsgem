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
        return OperationHistory::TYPE_UPDATE;
    }

    public function getUser(): ?User
    {
        return $this->tree->user;
    }

    public function getDelta(): ?array
    {
        // get latest tree operation history
        if (!($previousOperationHistory = $this->tree->operationHistories()->orderBy('id', 'desc')->first())) {
            return null;
        }

        $delta = [
            'remain' => $this->tree->remain - $previousOperationHistory->result_data->remain,
            'capacity' => $this->tree->capacity - $previousOperationHistory->result_data->capacity,
            'progress' => bcsub($this->tree->progress, $previousOperationHistory->result_data->progress, 1),
        ];

        if (bccomp($delta['progress'], '0.0', 1) === 0) {
            $delta['progress'] = null;
        }

        $delta = array_filter($delta);

        if (!count($delta)) {
            return null;
        }

        return $delta;
    }
}
