<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Response;

class TreeController extends Controller
{
    public function store(User $user)
    {
        $user->trees()->create();

        return response()->json([], Response::HTTP_CREATED);
    }
}
