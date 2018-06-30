<?php

namespace App\SettleUtils;

class TreeSettleResult
{
    public $award;

    public $updatedTrees;

    public $updatedWallets;

    public function __construct()
    {
        $this->award = 0;
        $this->updatedTrees = [];
        $this->updatedWallets = [];
    }
}