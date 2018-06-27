<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Wallet;

class WalletTransferRateController extends Controller
{
    public function index()
    {
        $gems = (new Wallet)->gems();

        $dictionary = collect($gems)->crossJoin($gems)->map(function ($pair) {
            return implode('_', $pair);
        });

        $dictionary = $dictionary->combine(array_fill(0, $dictionary->count(), '1.0'));

        return response()->json($dictionary);
    }
}
