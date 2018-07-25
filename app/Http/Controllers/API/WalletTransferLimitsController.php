<?php

namespace App\Http\Controllers\API;

use App\Computed\WalletTransferLimit;
use App\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class WalletTransferLimitsController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'from_gem' => 'required',
            'to_gem' => 'required',
        ]);

        $fromWallet = Wallet::where([
            ['gem', $request->from_gem],
            ['user_id', Auth::user()->id]
        ])->firstOrFail();

        $toWallet = Wallet::where([
            ['gem', $request->to_gem],
            ['user_id', Auth::user()->id]
        ])->firstOrFail();

        return response()->json(
            new WalletTransferLimit(Auth::user(), $toWallet)
        );
    }
}
