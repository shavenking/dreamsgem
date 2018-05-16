<?php

namespace App\Http\Controllers\API;

use App\Events\WalletRecalled;
use App\Http\Controllers\Controller;
use App\User;
use App\Wallet;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RecallController extends Controller
{
    public function store(User $user)
    {
        $this->authorize('recall', $user);

        DB::beginTransaction();

        try {
            foreach (
                (new Wallet)->gems()
                as $gem
            ) {
                $totalAmountGained = '0.0';

                foreach ($user->childAccounts as $childAccount) {
                    $wallet = Wallet::where([
                        'user_id' => $childAccount->id,
                        'gem' => $gem
                    ])->firstOrFail();

                    $totalAmountGained = bcadd(
                        $wallet->amount,
                        $totalAmountGained,
                        1
                    );

                    $affectedCount = Wallet::whereId($wallet->id)
                        ->where('gem', $wallet->gem)
                        ->where('amount', $wallet->amount)
                        ->update(
                            [
                                'amount' => '0.0',
                            ]
                        );

                    if (bccomp($totalAmountGained, '0.0', 1) !== 0) {
                        abort_if(
                            $affectedCount !== 1,
                            Response::HTTP_SERVICE_UNAVAILABLE,
                            'The wallet data has changed'
                        );

                        event(new WalletRecalled($wallet->refresh(), $user));
                    }
                }

                // $totalAmountGained from $gem
                $wallet = Wallet::where([
                    'user_id' => $user->id,
                    'gem' => $gem
                ])->firstOrFail();

                $affectedCount = Wallet::whereId($wallet->id)
                    ->where('gem', $wallet->gem)
                    ->where('amount', $wallet->amount)
                    ->update(
                        [
                            'amount' => $totalAmountGained,
                        ]
                    );

                if (bccomp($totalAmountGained, '0.0', 1) !== 0) {
                    abort_if(
                        $affectedCount !== 1,
                        Response::HTTP_SERVICE_UNAVAILABLE,
                        'The wallet data has changed'
                    );

                    event(new WalletRecalled($wallet->refresh(), $user));
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([], Response::HTTP_CREATED);
    }
}
