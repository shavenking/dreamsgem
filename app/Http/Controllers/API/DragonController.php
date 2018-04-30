<?php

namespace App\Http\Controllers\API;

use App\Events\DragonCreated;
use App\Events\WalletUpdated;
use App\Http\Controllers\Controller;
use App\User;
use App\Wallet;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DragonController extends Controller
{
    public function store(User $user)
    {
        $this->authorize('createDragons', $user);

        DB::beginTransaction();

        if (!$user->dragon) {
            $dragon = $user->dragon()->create();
            event(new DragonCreated($dragon, Auth::user()));

            if ($user->addTree()) {
                $wallet = $user->parent->wallets()->firstOrCreate(
                    [
                        'gem' => Wallet::GEM_DUO_CAI,
                    ], [
                        'amount' => '0',
                    ]
                );

                $affectedCount = Wallet::whereId($wallet->id)
                    ->where('gem', $wallet->gem)
                    ->where('amount', $wallet->amount)
                    ->update(
                        [
                            'amount' => bcadd($wallet->amount, Wallet::REWARD_ACTIVATE_DRAGON, 1),
                        ]
                    );

                if ($affectedCount !== 1) {
                    abort(503);
                }

                event(new WalletUpdated($wallet->refresh()));
            }
        }

        DB::commit();

        return response()->json([], Response::HTTP_CREATED);
    }
}
