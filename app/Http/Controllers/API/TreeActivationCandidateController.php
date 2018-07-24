<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Tree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TreeActivationCandidateController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'type' => 'required',
        ]);

        if (!is_array($request->input('type'))) {
            $types = [$request->input('type')];
        } else {
            $types = $request->input('type');
        }

        $userGroups = collect();

        foreach ($types as $type) {
            if (in_array($type, (new Tree)->types())) {
                $userGroups[$type] = $this->candidatesOf($type);
            }
        }

        return response()->json($userGroups);
    }

    private function candidatesOf($type)
    {
        $users = collect();

        if (
            Auth::user()->activated
            && !Auth::user()->frozen
            && (
                !($latestActivatedTree = Auth::user()->activatedTrees()->latest()->first())
                || $latestActivatedTree->type <= $type
            )
        ) {
            $users = $users->push(Auth::user());
        }

        $users = $users->merge(Auth::user()->childAccounts()->where('frozen', false)->whereHas('activatedDragon')->where(function ($query) use ($type) {
            $query->whereHas('activatedTrees', function ($query) use ($type) {
                $query->where('type', '>', $type);
            }, '=', 0)->orWhereHas('activatedTrees', null, '=', 0);
        })->get());

        $users = $users->merge(Auth::user()->descendants()->where('frozen', false)->whereHas('activatedDragon')->where(function ($query) use ($type) {
            $query->whereNull('user_id')->whereHas('activatedTrees',
                function ($query) use ($type) {
                    $query->where('type', '>', $type);
                }, '=', 0)->orWhereHas('activatedTrees', null, '=', 0);
        })->get());

        return $users;
    }
}
