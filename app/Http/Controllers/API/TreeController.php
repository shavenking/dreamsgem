<?php

namespace App\Http\Controllers\API;

use App\Events\TreeActivated;
use App\Events\TreeCreated;
use App\Http\Controllers\Controller;
use App\Tree;
use App\User;
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

        return response()->json($trees->paginate());
    }

    public function store(User $user)
    {
        $this->authorize('createTrees', $user);

        $tree = $user->trees()->create(
            [
                'remain' => User::DEFAULT_TREE_CAPACITY,
                'capacity' => User::DEFAULT_TREE_CAPACITY,
                'progress' => '0',
            ]
        );

        event(new TreeCreated($tree, $user));

        return response()->json($tree, Response::HTTP_CREATED);
    }

    public function update(User $user, Tree $tree, Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
        ]);

        $this->authorize('update', $tree);

        $targetUser = User::findOrFail($request->user_id);

        if (
            $targetUser->activatedTrees->count() >= User::MAX_ACTIVATE_TREE_AMOUNT
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

        return response()->json($tree, Response::HTTP_OK);
    }
}
