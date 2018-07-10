<?php

namespace App\Http\Controllers\API;

use App\Events\UserUpdated;
use App\Mail\PasswordReset;
use App\User;
use Exception;
use Faker\Generator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    public function store(Request $request, Generator $faker)
    {
        $this->validate($request, [
            'email' => 'required'
        ]);

        /** @var \App\User $user */
        $user = User::whereEmail($request->email)->firstOrFail();

        DB::beginTransaction();

        try {
            $user->update([
                'password' => Hash::make($newPassword = $faker->password),
            ]);

            event(new UserUpdated($user));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        Mail::to($user)->send(new PasswordReset($newPassword));

        return response()->json(null, Response::HTTP_CREATED);
    }
}
