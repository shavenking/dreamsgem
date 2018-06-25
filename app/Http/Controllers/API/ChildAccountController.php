<?php

namespace App\Http\Controllers\API;

use App\Events\UserCreated;
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

        DB::beginTransaction();

        $childAccount = $user->childAccounts()->create([
            'name' => 'dreamsgem',
            'email' => $faker->unique()->safeEmail,
            'password' => Hash::make($faker->password),
            'frozen' => false,
        ]);

        if ($request->has('upline_id')) {
            $upline = User::findOrFail($request->upline_id);
        } else {
            $upline = $user;
        }

        $upline->addDownline($childAccount);

        Wallet::where([
            'user_id' => $childAccount->id,
            'gem' => Wallet::GEM_DREAMSGEM
        ])->firstOrCreate([
            'user_id' => $childAccount->id,
            'gem' => Wallet::GEM_DREAMSGEM,
            'amount' => '0.0',
        ]);

        FreezeUser::dispatch($childAccount)->delay(Carbon::now()->addDays(7));

        event(new UserCreated($childAccount));

        DB::commit();

        return response()->json($childAccount, Response::HTTP_CREATED);
    }
}
