<?php

namespace App\Http\Controllers\API;

use App\Events\WalletTransferApplied;
use App\Events\WalletWithheld;
use App\Events\WithSubType;
use App\Http\Controllers\Controller;
use App\OperationHistory;
use App\Wallet;
use App\WalletTransferApplication;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WalletTransferApplicationController extends Controller
{
    public function index($gem, Request $request)
    {
        $wallet = Auth::user()->wallets()->whereGem($gem)->firstOrFail();

        // validate if current logged in user has permissions to transfer wallet
        $this->authorize('transfer', $wallet);

        $walletTransferApplications = $wallet->walletTransferApplications();

        if ($request->has('to_gem')) {
            $this->validate($request, [
                'to_gem' => 'array'
            ]);

            $walletIds = Auth::user()->wallets()->whereIn('gem', $request->to_gem)->pluck('id');

            $walletTransferApplications->whereIn('to_wallet_id', $walletIds);
        }

        return response()->json(
            $walletTransferApplications->orderBy('created_at', 'desc')->with('fromWallet', 'toWallet')->paginate()
        );
    }

    public function store($gem, Request $request)
    {
        $wallet = Auth::user()->wallets()->whereGem($gem)->firstOrFail();

        // validate if current logged in user has permissions to transfer wallet
        $this->authorize('transfer', $wallet);

        $this->validate($request, [
            'to_gem' => 'required',
            'amount' => 'required',
            'wallet_password' => 'required',
        ]);

        $toWallet = Auth::user()->wallets()->whereGem($request->to_gem)->firstOrFail();

        abort_if(
            !($pair = data_get((new Wallet)->transferRateTextMap(), "{$gem}:{$request->to_gem}"))
            || count($exploded = explode(':', $pair)) !== 2
            || count(list($fromRate, $toRate) = $exploded) !== 2,
            Response::HTTP_INTERNAL_SERVER_ERROR,
            trans('errors.Cannot find transfer rate')
        );

        abort_if(
            $wallet->id === $toWallet->id,
            Response::HTTP_BAD_REQUEST,
            trans('errors.You are not allowed to transfer to same wallet')
        );

        abort_if(
            $wallet->user_id !== $toWallet->user_id,
            Response::HTTP_BAD_REQUEST,
            trans('errors.You are not allowed to transfer to another member')
        );

        abort_if(
            $wallet->user->is_child_account
            || $toWallet->user->is_child_account,
            Response::HTTP_BAD_REQUEST,
            trans('errors.You are not allowed to transfer to wallets that belongs to child accounts')
        );

        abort_if(
            !$wallet->allowedToApplyTransfer(),
            Response::HTTP_BAD_REQUEST,
            trans('errors.from_wallet is not allowed to apply transfer')
        );

        abort_if(
            !$toWallet->allowedToGetTransferFrom($wallet),
            Response::HTTP_BAD_REQUEST,
            trans('errors.from_wallet is not allowed to transfer to to_wallet')
        );

        abort_if(
            bccomp($request->amount, '0.0', 1) <= 0,
            Response::HTTP_BAD_REQUEST,
            trans('errors.Amount must be greater than 0')
        );

        abort_if(
            bccomp($wallet->amount, $request->amount, 1) < 0,
            Response::HTTP_BAD_REQUEST,
            trans('errors.Insufficient balance')
        );

        abort_if(
            bccomp(bcmul(bcdiv($request->amount, $fromRate, 1), $fromRate, 1), $request->amount, 1) !== 0,
            Response::HTTP_BAD_REQUEST,
            trans('errors.Amount should be multiplier of :fromRate', compact('fromRate'))
        );

        abort_if(
            !Hash::check($request->wallet_password, Auth::user()->wallet_password),
            Response::HTTP_UNAUTHORIZED,
            trans('errors.Incorrect password')
        );

        DB::beginTransaction();

        try {
            // 預扣
            $affectedCount = Wallet::where([
                'id' => $wallet->id,
                'amount' => $wallet->amount,
            ])->update(['amount' => bcsub($wallet->amount, $request->amount, 1)]);

            abort_if(
                $affectedCount !== 1,
                Response::HTTP_SERVICE_UNAVAILABLE,
                trans('errors.Race condition')
            );

            event(
                new WithSubType(
                    new WalletWithheld($wallet->refresh(), $wallet->user),
                    OperationHistory::SUB_TYPE_WITHHELD
                )
            );

            $walletTransferApplication = WalletTransferApplication::create([
                'from_wallet_id' => $wallet->id,
                'to_wallet_id' => $toWallet->id,
                'status' => WalletTransferApplication::STATUS_PENDING,
                'rate' => $rate = bcdiv($toRate, $fromRate, 1),
                'amount' => bcmul($request->amount, $rate, 1),
            ]);

            event(
                new WalletTransferApplied($walletTransferApplication->refresh(), $wallet->user)
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $walletTransferApplication->setRelations([]);

        return response()->json($walletTransferApplication, Response::HTTP_CREATED);
    }
}
