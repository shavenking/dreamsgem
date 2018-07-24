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
            'type' => "required|in:" . implode(',', [Tree::TYPE_SMALL, Tree::TYPE_MEDIUM, Tree::TYPE_LARGE]),
        ]);

        $users = collect();

        if (
            Auth::user()->activated
            && !Auth::user()->frozen
            && (
                !($latestActivatedTree = Auth::user()->activatedTrees()->latest()->first())
                || $latestActivatedTree->type <= $request->input('type')
            )
        ) {
            $users = $users->push(Auth::user());
        }

        $users = $users->merge(Auth::user()->childAccounts()->where('frozen', false)->whereHas('activatedDragon')->where(function ($query) use ($request) {
            $query->whereHas('activatedTrees', function ($query) use ($request) {
                $query->where('type', '>', $request->input('type'));
            }, '=', 0)->orWhereHas('activatedTrees', null, '=', 0);
        })->get());

        $users = $users->merge(Auth::user()->descendants()->where('frozen', false)->whereHas('activatedDragon')->where(function ($query) use ($request) {
            $query->whereNull('user_id')->whereHas('activatedTrees',
                function ($query) use ($request) {
                    $query->where('type', '>', $request->input('type'));
                }, '=', 0)->orWhereHas('activatedTrees', null, '=', 0);
        })->get());

        return response()->json($users);
    }
}
