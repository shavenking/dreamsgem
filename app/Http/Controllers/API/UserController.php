<?php

namespace App\Http\Controllers\API;

use App\Events\UserCreated;
use App\Events\UserUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\FreezeUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $userTable = (new User)->getTable();

        $this->validate($request, [
            'email' => "required|email|unique:$userTable",
            'password' => 'required',
            'parent_id' => 'required',
        ]);

        DB::beginTransaction();

        $parentUser = User::findOrFail($request->parent_id);
        $user = User::create([
            'name' => 'dreamsgem',
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
}
