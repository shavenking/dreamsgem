<?php

namespace App\Http\Controllers\API;

use App\Dragon;
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

        if ($request->has('type')) {
            if (is_array($request->input('type'))) {
                $operationHistories->whereIn('type', $request->input('type'));
            } else {
                $operationHistories->where('type', $request->input('type'));
            }
        }

        if ($request->has('sub_type')) {
            if (is_array($request->input('sub_type'))) {
                $operationHistories->whereIn('sub_type', $request->input('sub_type'));
            } else {
                $operationHistories->where('sub_type', $request->input('sub_type'));
            }
        }

        if ($request->has('operatable_type')) {
            $operationHistories = $operationHistories->reverseOperatableType($request->operatable_type);

            if ($request->has('operatable_id')) {
                $operationHistories = $operationHistories->whereOperatableId($request->operatable_id);
            }
        }

        $operationHistories = $operationHistories->orderBy('id', 'desc')->with('operator', 'user')->paginate();
        $operationHistories->appends($request->all());
        $operationHistories->map(function ($operationHistory) {
            $operationHistory->setAttribute('operatable_type', $operationHistory->transformOperatableType($operationHistory->operatable_type));

            // 龍加入了 type 欄位，這段為舊的 OperationHistory 補上資料
            if (
                $operationHistory->operatable_type === $operationHistory->transformOperatableType('App\Dragon')
                && is_null(optional($operationHistory->result_data)->type)
            ) {
                $operationHistory->result_data = (object) array_merge((array) $operationHistory->result_data, [
                    'type' => Dragon::TYPE_NORMAL
                ]);
            }

            // 轉賬 TMD 要改成顯示接收方
            if (
                $operationHistory->operatable_type === $operationHistory->transformOperatableType('App\Wallet')
                && $operationHistory->type === OperationHistory::TYPE_TRANSFER
                && $operationHistory->operator->is($operationHistory->user)
            ) {
                $newOperationHistory = OperationHistory::with('user')->where([
                    ['operatable_type', 'App\Wallet'],
                    ['operator_id', $operationHistory->operator_id],
                    ['type', OperationHistory::TYPE_TRANSFER],
                    ['id', '>', $operationHistory->id]
                ])->first();

                $operationHistory->user_id = $newOperationHistory->user->id;
                $operationHistory->setRelation('user', $newOperationHistory->user);
            }

            return $operationHistory->setAttribute(
                'sub_type_string',
                $operationHistory->sub_type ? trans('sub-type.' . $operationHistory->sub_type) : null
            );
        });

        return response()->json($operationHistories);
    }
}
