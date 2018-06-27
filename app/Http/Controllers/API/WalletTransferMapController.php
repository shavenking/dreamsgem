<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Wallet;

class WalletTransferMapController extends Controller
{
    public function index()
    {
        return response()->json((new Wallet)->walletTransferMap()->filter(function ($candidate) {
            return count($candidate) !== 0;
        }));
    }
}
