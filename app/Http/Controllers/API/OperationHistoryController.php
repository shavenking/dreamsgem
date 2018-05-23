<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\OperationHistory;
use App\User;

class OperationHistoryController extends Controller
{
    public function index(User $user)
    {
        $this->authorize('listOperationHistories', $user);

        $operationHistories = OperationHistory::whereOperatorId($user->id)->orWhere('user_id', $user->id)->latest()->paginate();

        return response()->json($operationHistories);
    }
}
