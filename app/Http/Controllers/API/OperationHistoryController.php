<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\OperationHistory;
use App\User;
use Illuminate\Http\Request;

class OperationHistoryController extends Controller
{
    public function index(User $user, Request $request)
    {
        $this->authorize('listOperationHistories', $user);

        $operationHistories = OperationHistory::where(function ($query) use ($user) {
            $query->where('operator_id', $user->id)->orWhere('user_id', $user->id);
        });

        if ($request->has('operatable_type')) {
            $operationHistories = $operationHistories->reverseOperatableType($request->operatable_type);

            if ($request->has('operatable_id')) {
                $operationHistories = $operationHistories->whereOperatableId($request->operatable_id);
            }
        }

        $operationHistories = $operationHistories->orderBy('id', 'desc')->paginate();
        $operationHistories->appends($request->all());
        $operationHistories->map(function ($operationHistory) {
            return $operationHistory->setAttribute(
                'sub_type_string',
                $operationHistory->sub_type ? trans('sub-type.' . $operationHistory->sub_type) : null
            );
        });

        return response()->json($operationHistories);
    }
}
