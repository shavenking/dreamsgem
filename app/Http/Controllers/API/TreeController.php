<?php

namespace App\Http\Controllers\API;

use App\Events\TreeActivated;
use App\Events\TreeCreated;
use App\Events\WalletUpdated;
use App\Http\Controllers\Controller;
use App\Tree;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TreeController extends Controller
{
    public function index(User $user, Request $request)
    {
        $trees = Tree::query();

        if ($request->hasAny('owner_id', 'user_id')) {
            $trees->where($request->only('owner_id', 'user_id'));
        } else {
            $trees->where(function ($query) use ($user) {
                $query
                    ->where('owner_id', $user->id)
                    ->orWhere('user_id', $user->id);
            });
        }

        if ($request->has('activated')) {
            $trees->where('user_id', $request->activated ? '!=' : '=', null);
        }

        return response()->json($trees->with('owner', 'user')->paginate()->appends($request->only('owner_id', 'user_id', 'activated')));
    }

    public function store(User $user)
    {
        $this->authorize('createTrees', $user);

        DB::beginTransaction();

        try {
            $tree = $user->trees()->create(
                [
                    'remain' => User::DEFAULT_TREE_CAPACITY,
                    'capacity' => User::DEFAULT_TREE_CAPACITY,
                    'progress' => '0',
                ]
            );

            $wallet = Wallet::where([
                'user_id' => request()->user()->id,
                'gem' => Wallet::GEM_USD,
            ])->firstOrFail();

            abort_if(
                bccomp($wallet->amount, '1000.0', 1) < 0,
                Response::HTTP_BAD_REQUEST,
                'Amount is not enough'
            );

            $affectedCount = Wallet::where([
                'id' => $wallet->id,
                'amount' => $wallet->amount,
            ])->update([
                'amount' => bcsub($wallet->amount, '1000.0', 1)
            ]);

            event(new WalletUpdated($wallet->refresh(), request()->user()));

            abort_if($affectedCount !== 1, Response::HTTP_SERVICE_UNAVAILABLE, 'The data is changed.');

            event(new TreeCreated($tree, $user));
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        DB::commit();

        return response()->json($tree->load('owner', 'user'), Response::HTTP_CREATED);
    }

    public function update(User $user, Tree $tree, Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
        ]);

        $this->authorize('update', $tree);

        $targetUser = User::findOrFail($request->user_id);

        if (
            !$targetUser->activated
            || $targetUser->activatedTrees->count() >= User::MAX_ACTIVATE_TREE_AMOUNT
            || $tree->activated
            || ($user->id !== Auth::user()->id && !$user->childAccounts()->whereId($targetUser->id)->first())
        ) {
            return response()->json([], 400);
        }

        DB::beginTransaction();

        abort_if(
            1 !== $user->activateTree($tree, $targetUser),
            503
        );

        event(new TreeActivated($tree->refresh(), $user));

        DB::commit();

        return response()->json($tree->load('owner', 'user'), Response::HTTP_OK);
    }
}
