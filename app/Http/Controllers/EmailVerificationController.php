<?php

namespace App\Http\Controllers;

use App\EmailVerification;
use App\Events\UserUpdated;
use App\Events\WithSubType;
use App\OperationHistory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailVerificationController extends Controller
{
    public function update($token)
    {
        $emailVerification = EmailVerification::whereToken($token)->firstOrFail();

        DB::beginTransaction();

        try {
            $emailVerification->user->update([
                'email_verified' => true,
            ]);

            event(
                new WithSubType(
                    new UserUpdated($emailVerification->user, $emailVerification->user),
                    OperationHistory::SUB_TYPE_EMAIL_VERIFIED
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('index');
    }
}
