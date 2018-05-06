<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;

class WalletController extends Controller
{
    public function index(User $user)
    {
        $wallets = $user->wallets()->paginate();

        return response()->json($wallets);
    }
}
