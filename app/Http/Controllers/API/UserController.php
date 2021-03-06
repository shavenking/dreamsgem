<?php

namespace App\Http\Controllers\API;

use App\Events\UserCreated;
use App\Events\UserUpdated;
use App\Events\WalletCreated;
use App\Http\Controllers\Controller;
use App\Jobs\FreezeUser;
use App\Jobs\SendVerifyEmail;
use App\Tree;
use App\User;
use App\Wallet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware(['auth:api'])->except(['store']);
        $this->middleware(['email.verified'])->except(['show', 'store']);

        // HOTFIX
        if ($request->child_account_id) {
            $this->middleware(['auth:api', 'email.verified']);
        }
    }

    public function show(User $user, Request $request)
    {
        if ($user->id !== Auth::user()->id && !Auth::user()->email_verified) {
            abort(Response::HTTP_FORBIDDEN, trans('errors.User email not verified'));
        }

        return response()->json($user->setAttribute('downlines', $user->downlines));
    }

    public function store(Request $request)
    {
        $userTable = (new User)->getTable();

        $this->validate($request, [
            'name' => 'required',
            'email' => "required|email|unique:$userTable",
            'password' => 'required',
            'upline_id' => 'required_without_all:child_account_id',
            'child_account_id' => 'required_without_all:upline_id',
        ]);

        DB::beginTransaction();

        if (request()->has('upline_id')) {
            $user = $this->createUser($request);
        } else {
            $user = $this->createUserFromChildAccount($request);
        }

        DB::commit();

        return response()->json($user, Response::HTTP_CREATED);
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        foreach (['password', 'wallet_password'] as $possiblePasswordResetKey) {
            if ($request->has($possiblePasswordResetKey)) {
                abort_if(
                    !Hash::check($request->{$possiblePasswordResetKey}, $user->{$possiblePasswordResetKey}),
                    Response::HTTP_UNAUTHORIZED,
                    trans('errors.Incorrect password')
                );

                $user->{$possiblePasswordResetKey} = Hash::make($request->{"new_$possiblePasswordResetKey"});
            }
        }

        if ($user->isDirty()) {
            DB::transaction(function () use ($user) {
                $user->save();

                event(new UserUpdated($user, $user));
            });
        }

        return response()->json($user, 200);
    }

    private function createUser(Request $request): User
    {
        $parentUser = User::findOrFail($request->upline_id);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'wallet_password' => Hash::make($request->wallet_password ?? $request->password),
            'frozen' => false,
        ]);

        $parentUser->addDownline($user);

        event(new UserCreated($user));

        foreach ((new Wallet)->gems() as $gem) {
            $wallet = $user->wallets()->firstOrCreate(
                [
                    'gem' => $gem,
                ], [
                    'amount' => '0',
                ]
            );

            event(new WalletCreated($wallet));
        }

        FreezeUser::dispatch($user)->delay(Carbon::now()->addDays(7));
        SendVerifyEmail::dispatch($user);

        return $user;
    }

    private function createUserFromChildAccount(Request $request): User
    {
        $childAccount = User::findOrFail($request->child_account_id);
        $this->authorize('updateChildAccounts', $childAccount);
        $childAccount->user_id = null;
        $childAccount->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'wallet_password' => Hash::make($request->wallet_password ?? $request->password),
        ]);

        event(new UserUpdated($childAccount, Auth::user()));

        return $childAccount;
    }

    public function availableTreeTypes(User $user)
    {
        return response()->json((new Tree)->treeTypes());
    }
}
