<?php

namespace App\Http\Controllers\API;

use App\Events\TreeActivated;
use App\Events\TreeCreated;
use App\Events\WalletUpdated;
use App\Events\WithSubType;
use App\Http\Controllers\Controller;
use App\OperationHistory;
use App\Tree;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        if ($request->has('remain_available')) {
            $trees->where('remain', $request->remain_available ? '>' : '=', 0);
        }

        return response()->json($trees->with('owner', 'user')->paginate()->appends($request->only('owner_id', 'user_id',
            'activated')));
    }

    public function store(User $user, Request $request)
    {
        $this->authorize('createTrees', $user);

        DB::beginTransaction();

        $treeType = $request->input('type', Tree::TYPE_SMALL);
        $treePrice = (new Wallet)->treePrice($treeType);

        // 買了升等的樹未來只買能同等級或著更高等級
        abort_if(
            ($latestTree = $user->trees()->latest()->first())
            && $latestTree->typeIsGreaterThan($treeType),
            Response::HTTP_BAD_REQUEST,
            trans('errors.Not allowed to buy smaller tree')
        );

        try {
            $tree = $user->trees()->create(
                [
                    'type' => $treeType,
                    'remain' => (new Tree)->treeCapacity($treeType),
                    'capacity' => (new Tree)->treeCapacity($treeType),
                    'progress' => '0',
                ]
            );

            $wallet = Wallet::where([
                'user_id' => request()->user()->id,
                'gem' => Wallet::GEM_DREAMSGEM,
            ])->firstOrFail();

            abort_if(
                bccomp($wallet->amount, $treePrice, 1) < 0,
                Response::HTTP_BAD_REQUEST,
                'Amount is not enough'
            );

            $affectedCount = Wallet::where([
                'id' => $wallet->id,
                'amount' => $wallet->amount,
            ])->update([
                'amount' => bcsub($wallet->amount, $treePrice, 1)
            ]);

            event(
                new WithSubType(
                    new WalletUpdated($wallet->refresh(), request()->user()),
                    OperationHistory::SUB_TYPE_BUY_TREE
                )
            );

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

        $this->setTreeActivateAward($targetUser);
        $this->setTreeActivateAward($targetUser->parent);

        DB::commit();

        return response()->json($tree->load('owner', 'user'), Response::HTTP_OK);
    }

    private function setTreeActivateAward(?User $user)
    {
        if (!$user) {
            return;
        }

        $wallet = $user->wallets()->firstOrCreate(
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
                    'amount' => bcadd($wallet->amount, Wallet::REWARD_ACTIVATE_TREE, 1),
                ]
            );

        if ($affectedCount !== 1) {
            abort(503);
        }

        event(
            new WithSubType(
                new WalletUpdated($wallet->refresh()),
                OperationHistory::SUB_TYPE_AWARD_ACTIVATE_TREE
            )
        );
    }
}
