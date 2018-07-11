<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Wallet;

class WalletTransferRateController extends Controller
{
    public function index()
    {
        return response()->json((new Wallet)->transferRateTextMap());
    }
}
