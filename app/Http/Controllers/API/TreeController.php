<?php

namespace App\Http\Controllers\API;

use App\Events\TreeActivated;
use App\Events\TreeCreated;
use App\Http\Controllers\Controller;
use App\Tree;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TreeController extends Controller
{
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

        return response()->json([], Response::HTTP_CREATED);
    }

    public function update(User $user, Tree $tree, Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
        ]);

        $this->authorize('update', $tree);

        $targetUser = User::findOrFail($request->user_id);

        if ($targetUser->activatedTrees->count() >= User::MAX_ACTIVATE_TREE_AMOUNT) {
            return response()->json([], 400);
        }

        DB::beginTransaction();

        abort_if(
            1 !== $user->activateTree($tree, $targetUser),
            503
        );

        event(new TreeActivated($tree->refresh(), $user));

        DB::commit();

        return response()->json([], Response::HTTP_OK);
    }
}
