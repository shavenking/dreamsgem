<?php

namespace App\Http\Controllers\API;

use App\Events\WalletTransferred;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TransferController extends Controller
{
    public function store(Wallet $wallet, Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'amount' => 'required',
            'wallet_password' => 'required',
        ]);

        abort_if(
            $request->user()->id === (int) $request->user_id,
            Response::HTTP_BAD_REQUEST,
            'You are not allowed to transfer to yourself'
        );

        abort_if(
            bccomp($request->amount, '0.0', 1) <= 0,
            Response::HTTP_BAD_REQUEST,
            'Amount must be greater than 0'
        );

        // validate if current logged in user has permissions to transfer wallet
        $this->authorize('transfer', $wallet);

        // only usd wallet can be transfer
        abort_if(
            $wallet->gem !== Wallet::GEM_DREAMSGEM,
            Response::HTTP_BAD_REQUEST,
            'Only Dreamsgem wallet can be transfer'
        );

        abort_if(
            !Hash::check($request->wallet_password, Auth::user()->wallet_password),
            Response::HTTP_UNAUTHORIZED,
            trans('errors.Incorrect password')
        );

        // validate if user are not child accounts
        $targetUser = User::findOrFail($request->user_id);

        abort_if(
            $request->user()->is_child_account
            || $targetUser->is_child_account,
            Response::HTTP_BAD_REQUEST,
            'Child accounts are not allowed to transfer'
        );

        abort_if(
            !Auth::user()->isDescendantOf($targetUser)
            && !$targetUser->isDescendantOf(Auth::user()),
            Response::HTTP_BAD_REQUEST,
            trans('errors.target user should be downlines or uplines')
        );

        abort_if(
            bccomp($wallet->amount, $request->amount, 1) < 0,
            Response::HTTP_BAD_REQUEST,
            'Amount is not enough'
        );

        DB::beginTransaction();

        $affectedCount = 0;
        try {
            $affectedCount += Wallet::where([
                'id' => $wallet->id,
                'amount' => $wallet->amount,
            ])->update(['amount' => bcsub($wallet->amount, $request->amount, 1)]);

            event(new WalletTransferred($wallet->refresh(), $request->user()));

            $targetWallet = Wallet::where([
                'user_id' => $targetUser->id,
                'gem' => Wallet::GEM_DREAMSGEM,
            ])->firstOrFail();

            $affectedCount += Wallet::where([
                'id' => $targetWallet->id,
                'amount' => $targetWallet->amount,
            ])->update(['amount' => bcadd($targetWallet->amount, $request->amount, 1)]);

            event(new WalletTransferred($targetWallet->refresh(), $request->user()));

            abort_if(
                $affectedCount !== 2,
                Response::HTTP_SERVICE_UNAVAILABLE,
                'The wallet data has changed'
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json($wallet->refresh(), Response::HTTP_CREATED);
    }
}
