<?php

namespace App\Http\Controllers\API;

use App\Events\UserCreated;
use App\Http\Controllers\Controller;
use App\Jobs\FreezeUser;
use App\User;
use Faker\Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ChildAccountController extends Controller
{
    public function index(User $user)
    {
        $childAccounts = $user->childAccounts()->paginate();

        return response()->json($childAccounts);
    }

    public function store(User $user, Generator $faker)
    {
        $this->authorize('createChildAccount', $user);

        DB::beginTransaction();

        $childAccount = $user->childAccounts()->create([
            'name' => 'dreamsgem',
            'email' => $faker->unique()->safeEmail,
            'password' => Hash::make($faker->password),
            'frozen' => false,
        ]);

        $user->addDownline($childAccount);

        FreezeUser::dispatch($childAccount)->delay(Carbon::now()->addDays(7));

        event(new UserCreated($childAccount));

        DB::commit();

        return response()->json($childAccount, Response::HTTP_CREATED);
    }
}
