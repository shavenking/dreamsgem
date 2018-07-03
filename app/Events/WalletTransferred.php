<?php

namespace App\Events;

use App\Operatable;
use App\OperationHistory;
use App\User;
use App\Wallet;

class WalletTransferred implements ShouldCreateOperationHistory
{
    public $wallet;

    public $operator;

    /**
     * Create a new event instance.
     *
     * @param Wallet $wallet
     * @param User   $operator
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
        return OperationHistory::TYPE_TRANSFER;
    }

    public function getUser(): ?User
    {
        return $this->wallet->user;
    }

    public function getDelta(): ?array
    {
        // get latest wallet operation history
        if (!($previousOperationHistory = $this->wallet->operationHistories()->orderBy('id', 'desc')->first())) {
            return null;
        }

        $delta = [
            'amount' => bcsub($this->wallet->amount, $previousOperationHistory->result_data->amount, 1),
        ];

        if (bccomp($delta['amount'], '0.0', 1) === 0) {
            $delta['amount'] = null;
        }

        $delta = array_filter($delta);

        if (!count($delta)) {
            return null;
        }

        return $delta;
    }
}
