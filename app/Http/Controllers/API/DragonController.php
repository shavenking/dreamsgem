<?php

namespace App\Http\Controllers\API;

use App\Dragon;
use App\Events\DragonActivated;
use App\Events\DragonCreated;
use App\Events\WalletUpdated;
use App\Events\WithSubType;
use App\Http\Controllers\Controller;
use App\OperationHistory;
use App\Tree;
use App\User;
use App\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $appends = [];

        if ($request->has('owner_id')) {
            $appends['owner_id'] = $request->owner_id;
            $dragons->whereOwnerId($request->owner_id);
        }

        if ($request->has('user_id')) {
            $appends['user_id'] = $request->user_id;
            $dragons->whereUserId($request->user_id);
        }

        if ($request->has('activated')) {
            $appends['activated'] = $request->activated;
            $dragons->where('user_id', $request->activated ? '!=' : '=', null);
        }

        return response()->json($dragons->with('owner', 'user')->paginate()->appends($appends));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'owner_id' => 'required',
        ]);

        DB::beginTransaction();

        $dragon = Dragon::availableForBuying()->firstOrCreate([], ['type' => Dragon::TYPE_NORMAL]);

        $this->buyDragon($dragon, User::findOrFail($request->owner_id));

        DB::commit();

        return response()->json($dragon->refresh()->load('owner', 'user'), Response::HTTP_OK);
    }

    public function update(Dragon $dragon, Request $request)
    {
        $this->validate($request, [
            'owner_id' => 'required_without_all:user_id',
            'user_id' => 'required_without_all:owner_id',
        ]);

        DB::beginTransaction();

        if ($request->has('owner_id')) {
            abort_if(
                $dragon->owner,
                Response::HTTP_BAD_REQUEST,
                trans('errors.Dragon already has owner')
            );

            $this->buyDragon($dragon, User::findOrFail($request->owner_id));
        }

        if ($request->has('user_id')) {
            $this->authorize('update', $dragon);

            abort_if(
                $dragon->user,
                Response::HTTP_BAD_REQUEST,
                trans('errors.Dragon already been activated')
            );

            $this->activateDragon($dragon, User::findOrFail($request->user_id));
        }

        DB::commit();

        return response()->json($dragon->refresh()->load('owner', 'user'), Response::HTTP_OK);
    }

    private function buyDragon(Dragon $dragon, User $owner)
    {
        $affectedCount = 0;

        $affectedCount += Dragon::where(array_only($dragon->toArray(), ['id', 'owner_id', 'user_id']))->update([
            'owner_id' => $owner->id,
        ]);

        $wallet = Wallet::where([
            'user_id' => request()->user()->id,
            'gem' => Wallet::GEM_DREAMSGEM,
        ])->firstOrFail();

        $price = '100.0';

        abort_if(
            bccomp($wallet->amount, $price, 1) < 0,
            Response::HTTP_BAD_REQUEST,
            trans('errors.Insufficient balance')
        );

        $affectedCount += Wallet::where([
            'id' => $wallet->id,
            'amount' => $wallet->amount,
        ])->update([
            'amount' => bcsub($wallet->amount, $price, 1)
        ]);

        event(
            new WithSubType(
                new WalletUpdated($wallet->refresh(), request()->user()),
                OperationHistory::SUB_TYPE_BUY_DRAGON
            )
        );

        abort_if(
            $affectedCount !== 2,
            Response::HTTP_SERVICE_UNAVAILABLE,
            trans('errors.Race condition')
        );

        event(new DragonCreated($dragon, Auth::user()));
    }

    private function activateDragon(Dragon $dragon, User $user)
    {
        abort_if(
            !$dragon->owner_id,
            Response::HTTP_BAD_REQUEST,
            trans('errors.You do not own the dragon')
        );

        abort_if(
            $user->activatedDragon,
            Response::HTTP_BAD_REQUEST,
            trans('errors.You already has activated dragon')
        );

        abort_if(
            $dragon->activated,
            Response::HTTP_BAD_REQUEST,
            trans('errors.Dragon already has been activated')
        );

        if (
            Auth::user()->id !== $user->id
            && !$user->isDescendantOf(Auth::user())
            && $user->user_id !== Auth::user()->id
        ) {
            abort(
                Response::HTTP_BAD_REQUEST,
                trans('errors.Invalid target user')
            );
        }

        abort_if(
            1 !== $dragon->activateDragon($user),
            Response::HTTP_SERVICE_UNAVAILABLE,
            trans('errors.Race condition')
        );

        event(new DragonActivated($dragon, Auth::user()));

        if ((int) $dragon->type === Dragon::TYPE_SMALL) {
            return;
        }

        $wallet = $user->wallets()->whereGem(Wallet::GEM_DUO_CAI)->firstOrFail();

        $affectedCount = Wallet::whereId($wallet->id)
            ->where('gem', $wallet->gem)
            ->where('amount', $wallet->amount)
            ->update(
                [
                    'amount' => bcadd($wallet->amount, Wallet::REWARD_ACTIVATE_DRAGON, 1),
                ]
            );

        abort_if(
            1 !== $affectedCount,
            Response::HTTP_SERVICE_UNAVAILABLE,
            trans('errors.Race condition')
        );

        event(
            new WithSubType(
                new WalletUpdated($wallet->refresh()),
                OperationHistory::SUB_TYPE_AWARD_ACTIVATE_DRAGON
            )
        );
    }
}
