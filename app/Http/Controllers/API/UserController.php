<?php

namespace App\Http\Controllers\API;

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
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        DB::beginTransaction();

        $user = User::create([
            'name' => 'dreamsgem',
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'frozen' => false,
        ]);

        if ($request->has('parent_id')) {
            $parentUser = User::findOrFail($request->parent_id);
            $parentUser->appendNode($user);
        }

        FreezeUser::dispatch($user)->delay(Carbon::now()->addDays(7));

        DB::commit();

        return response()->json([], Response::HTTP_CREATED);
    }
}
