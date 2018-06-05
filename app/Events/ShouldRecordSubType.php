<?php

namespace App\Events;

interface SubTypeAware
{
    public function subType(): ?int;
}