<?php

namespace App\Events;

use App\Operatable;
use App\OperatableTrait;
use App\OperationHistory;
use App\User;
use App\WalletTransferApplication;

class WalletTransferApplied implements ShouldCreateOperationHistory
{
    public $walletTransferApplication;

    public $operator;

    /**
     * Create a new event instance.
     *
     * @param WalletTransferApplication $walletTransferApplication
     * @param User                      $operator
     */
    public function __construct(WalletTransferApplication $walletTransferApplication, User $operator = null)
    {
        $this->walletTransferApplication = $walletTransferApplication->refresh();
        $this->operator = optional($operator)->refresh();
    }

    public function getOperatable(): Operatable
    {
        return $this->walletTransferApplication;
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
        return $this->walletTransferApplication->toWallet->user;
    }

    public function getDelta(): ?array
    {
        return null;
    }
}
