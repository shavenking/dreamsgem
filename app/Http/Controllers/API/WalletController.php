<?php

namespace App\Http\Controllers\API;

use App\Events\WalletUpdated;
use App\Events\WithSubType;
use App\Http\Controllers\Controller;
use App\OperationHistory;
use App\User;
use App\Wallet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index(User $user)
    {
        $wallets = $user->wallets()->paginate();

        return response()->json($wallets);
    }

    public function update(User $user, Wallet $wallet, Request $request)
    {
        $this->authorize('owns', $wallet);

        $this->validate($request, [
            'external_address' => 'max:255',
        ]);

        abort_if(
            !$wallet->external_wallet,
            Response::HTTP_BAD_REQUEST,
            trans('errors.Only external wallets can set external address')
        );

        $updated = false;

        DB::beginTransaction();

        try {
            if ($request->has('external_address') && $request->external_address !== $wallet->external_address) {
                $wallet->update([
                    'external_address' => $request->external_address,
                ]);

                $updated = true;
            }

            if ($updated) {
                event(
                    new WithSubType(
                        new WalletUpdated($wallet, Auth::user()),
                        OperationHistory::SUB_TYPE_UPDATE_EXTERNAL_ADDRESS
                    )
                );
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json($wallet->attributesToArray());
    }
}
