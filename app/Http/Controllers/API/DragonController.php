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
    public function index(Request $request)
    {
        if (!$request->hasAny(['owner_id', 'user_id'])) {
            $dragons = Dragon::whereOwnerId(null)->whereUserId(null)->paginate();

            return response()->json($dragons);
        }

        $dragons = Dragon::query();

        if ($request->has('owner_id')) {
            $dragons->whereOwnerId($request->owner_id);
        }

        if ($request->has('user_id')) {
            $dragons->whereUserId($request->user_id);
        }

        return response()->json($dragons->paginate());
    }

    public function update(Dragon $dragon, Request $request)
    {
        $this->validate($request, [
            'owner_id' => 'required_without_all:user_id',
            'user_id' => 'required_without_all:owner_id',
        ]);

        DB::beginTransaction();

        if ($request->has('owner_id')) {
            $this->buyDragon($dragon, User::findOrFail($request->owner_id));
        }

        if ($request->has('user_id')) {
            $this->authorize('update', $dragon);

            $this->activateDragon($dragon, User::findOrFail($request->user_id));
        }

        DB::commit();

        return response()->json($dragon->refresh(), Response::HTTP_OK);
    }

    private function buyDragon(Dragon $dragon, User $owner)
    {
        $affectedCount = Dragon::where(array_only($dragon->toArray(), ['id', 'owner_id', 'user_id']))->update([
            'owner_id' => $owner->id,
        ]);

        abort_if($affectedCount !== 1, Response::HTTP_SERVICE_UNAVAILABLE, 'The data is changed.');

        event(new DragonCreated($dragon, Auth::user()));
    }

    private function activateDragon(Dragon $dragon, User $user)
    {
        if (!$dragon->owner_id || $user->activatedDragon || $dragon->activated) {
            abort(Response::HTTP_BAD_REQUEST);
        }

        if (Auth::user()->id !== $user->id && !$user->isDescendantOf(Auth::user())) {
            abort(Response::HTTP_BAD_REQUEST);
        }

        abort_if(1 !== $dragon->activateDragon($user), Response::HTTP_SERVICE_UNAVAILABLE, 'The data is changed');

        event(new DragonActivated($dragon, Auth::user()));

        $user->trees()->create([
            'user_id' => $user->id,
            'progress' => '0',
            'remain' => User::DEFAULT_TREE_CAPACITY,
            'capacity' => User::DEFAULT_TREE_CAPACITY,
        ]);

        foreach ((new Wallet)->gems() as $gem) {
            $wallet = $user->wallets()->firstOrCreate(
                [
                    'gem' => $gem,
                ], [
                    'amount' => '0',
                ]
            );

            event(new WalletUpdated($wallet));
        }

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
