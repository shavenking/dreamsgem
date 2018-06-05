<?php

namespace App\Events;

use App\Operatable;
use App\User;

class WithSubType implements ShouldCreateOperationHistory, SubTypeAware
{
    public $decoratable;
    public $subType;

    public function __construct(ShouldCreateOperationHistory $decoratable, $subType)
    {
        $this->decoratable = $decoratable;
        $this->subType = $subType;
    }

    public function getUser(): ?User
    {
        return $this->decoratable->getUser();
    }

    public function getOperatable(): Operatable
    {
        return $this->decoratable->getOperatable();
    }

    public function getOperator(): ?User
    {
        return $this->decoratable->getOperator();
    }

    public function getType(): int
    {
        return $this->decoratable->getType();
    }

    public function getDelta(): ?array
    {
        return $this->decoratable->getDelta();
    }

    public function subType(): ?int
    {
        return $this->subType;
    }
}