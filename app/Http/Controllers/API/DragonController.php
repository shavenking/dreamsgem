<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Response;

class DragonController extends Controller
{
    public function store(User $user)
    {
        $user->dragon()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        return response()->json([], Response::HTTP_CREATED);
    }
}
