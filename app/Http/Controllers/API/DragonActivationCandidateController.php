<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DragonActivationCandidateController extends Controller
{
    public function index()
    {
        $users = collect();

        if (!Auth::user()->activated && !Auth::user()->frozen) {
            $users = $users->push(Auth::user());
        }

        foreach (Auth::user()->childAccounts as $childAccount) {
            if (!$childAccount->activated && !$childAccount->frozen) {
                $users->push($childAccount);
            }
        }

        $users = $users->merge(Auth::user()->descendants()->whereNull('user_id')->where('frozen', false)->whereHas('activatedDragon', null, '=', 0)->get());

        return response()->json($users);
    }
}
