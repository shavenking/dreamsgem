<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DragonActivationCandidateController extends Controller
{
    public function index()
    {
        $users = collect();

        if (!Auth::user()->activated) {
            $users = $users->push(Auth::user());
        }

        foreach (Auth::user()->childAccounts as $childAccount) {
            if (!$childAccount->activated) {
                $users->push($childAccount);
            }
        }

        $users = $users->merge(Auth::user()->descendants()->whereNull('user_id')->whereHas('activatedDragon', null, '=', 0)->get());

        return response()->json($users);
    }
}
