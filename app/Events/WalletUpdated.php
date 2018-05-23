<?php

namespace App\Events;

use App\Operatable;
use App\OperationHistory;
use App\User;
use App\Wallet;

class WalletUpdated implements ShouldCreateOperationHistory
{
    public $wallet;

    public $operator;

    /**
     * Create a new event instance.
     *
     * @param Wallet $wallet
     * @param User $operator
     */
    public function __construct(Wallet $wallet, User $operator = null)
    {
        $this->wallet = $wallet->refresh();
        $this->operator = optional($operator)->refresh();
    }

    public function getOperatable(): Operatable
    {
        return $this->wallet;
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
        return $this->wallet->user;
    }
}
