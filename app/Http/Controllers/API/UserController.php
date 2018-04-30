<?php

namespace App\Http\Controllers\API;

use App\Events\UserCreated;
use App\Events\UserUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\FreezeUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function show(User $user)
    {
        return response()->json(array_merge($user->toArray(), [
            'downlines' => $user->children,
        ]));
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
            $this->createUser($request);
        } else {
            $this->createUserFromChildAccount($request);
        }

        DB::commit();

        return response()->json([], Response::HTTP_CREATED);
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);

        if ($request->has('name') && $request->name !== $user->name) {
            $user->update(['name' => $request->name]);
            event(new UserUpdated($user, $user));
        }

        return response()->json([], 200);
    }

    private function createUser(Request $request)
    {
        $parentUser = User::findOrFail($request->upline_id);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'frozen' => false,
        ]);

        $possibleParents = collect([$parentUser]);

        while ($possibleParent = $possibleParents->shift()) {
            $children = $possibleParent->children()->get();

            if ($children->count() === User::MAX_CHILDREN_FOR_ONE_USER) {
                $possibleParents = $possibleParents->merge($children);
            } else {
                break;
            }
        }

        $possibleParent->appendNode($user);

        FreezeUser::dispatch($user)->delay(Carbon::now()->addDays(7));

        event(new UserCreated($user));
    }

    private function createUserFromChildAccount(Request $request)
    {
        $childAccount = User::findOrFail($request->child_account_id);
        $this->authorize('updateChildAccounts', $childAccount);
        $childAccount->user_id = null;
        $childAccount->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new UserUpdated($childAccount, Auth::user()));
    }
}
