<?php

namespace App\Http\Controllers\API;

use App\Events\DragonCreated;
use App\Http\Controllers\Controller;
use App\User;
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

            $user->addTree();
        }

        DB::commit();

        return response()->json([], Response::HTTP_CREATED);
    }
}
