<?php

namespace App\Http\Controllers\API;

use App\Dragon;
use App\Events\DragonActivated;
use App\Events\DragonCreated;
use App\Events\WalletUpdated;
use App\Http\Controllers\Controller;
use App\Tree;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DragonController extends Controller
{
    public function store(User $user)
    {
        $this->authorize('createDragons', $user);

        DB::beginTransaction();

        $dragon = $user->dragons()->create();
        event(new DragonCreated($dragon, Auth::user()));

        DB::commit();

        return response()->json([], Response::HTTP_CREATED);
    }

    public function update(User $user, Dragon $dragon, Request $request)
    {
        $this->validate($request, [
           'user_id' => 'required',
        ]);

        $this->authorize('update', $dragon);

        $targetUser = User::findOrFail($request->user_id);

        if ($targetUser->activatedDragon || $dragon->activated || !$targetUser->isDescendantOf($user)) {
            return response()->json([], 400);
        }

        DB::beginTransaction();

        if (1 === $user->activateDragon($dragon, $targetUser)) {
            event(new DragonActivated($dragon, $user));

            $targetUser->trees()->create([
                'user_id' => $targetUser->id,
                'progress' => '0',
                'remain' => User::DEFAULT_TREE_CAPACITY,
                'capacity' => User::DEFAULT_TREE_CAPACITY,
            ]);

            $wallet = $targetUser->parent->wallets()->firstOrCreate(
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

        DB::commit();

        return response()->json([], Response::HTTP_OK);
    }
}
