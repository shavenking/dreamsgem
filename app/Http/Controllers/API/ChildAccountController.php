<?php

namespace App\Http\Controllers\API;

use App\Events\UserCreated;
use App\Events\WalletCreated;
use App\Http\Controllers\Controller;
use App\Jobs\FreezeUser;
use App\User;
use App\Wallet;
use Faker\Generator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ChildAccountController extends Controller
{
    public function index(User $user, Request $request)
    {
        $childAccounts = $user->childAccounts();

        if ($request->hello === 'world') {
            $childAccounts = $childAccounts->get();
        } else {
            $childAccounts = $childAccounts->paginate();
        }

        return response()->json($childAccounts);
    }

    public function store(User $user, Generator $faker, Request $request)
    {
        $this->authorize('createChildAccount', $user);

        $faker->seed();

        DB::beginTransaction();

        $childAccount = $user->childAccounts()->create([
            'name' => 'dreamsgem',
            'email' => $faker->unique()->safeEmail,
            'password' => Hash::make($faker->password),
            'wallet_password' => Hash::make('88888888'),
            'frozen' => false,
        ]);

        if ($request->has('upline_id')) {
            $upline = User::findOrFail($request->upline_id);
        } else {
            $upline = $user;
        }

        abort_if(
            !$upline->isDescendantOf($user)
            && !$upline->is($user),
            \Illuminate\Http\Response::HTTP_BAD_REQUEST,
            trans('errors.Upline should be downlines or parent account')
        );

        abort_if(
            !$upline->activated,
            \Illuminate\Http\Response::HTTP_BAD_REQUEST,
            trans('errors.Upline not activated')
        );

        $upline->addDownline($childAccount);

        event(new UserCreated($childAccount));

        foreach ((new Wallet)->gems() as $gem) {
            $wallet = $childAccount->wallets()->firstOrCreate(
                [
                    'gem' => $gem,
                ], [
                    'amount' => '0',
                ]
            );

            event(new WalletCreated($wallet));
        }

        FreezeUser::dispatch($childAccount)->delay(Carbon::now()->addDays(7));

        DB::commit();

        return response()->json($childAccount, Response::HTTP_CREATED);
    }
}
