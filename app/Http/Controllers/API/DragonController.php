<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DragonController extends Controller
{
    public function store(User $user)
    {
        DB::beginTransaction();

        if (!$user->dragon) {
            $user->dragon()->create();
            $user->addTree();
        }

        DB::commit();

        return response()->json([], Response::HTTP_CREATED);
    }
}
