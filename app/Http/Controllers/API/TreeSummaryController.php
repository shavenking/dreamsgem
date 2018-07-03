<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Tree;
use App\User;
use Illuminate\Support\Facades\DB;

class TreeSummaryController extends Controller
{
    public function index(User $user)
    {
        $availableTrees = collect([
            (object) ['type' => Tree::TYPE_SMALL, 'amount' => 0],
            (object) ['type' => Tree::TYPE_MEDIUM, 'amount' => 0],
            (object) ['type' => Tree::TYPE_LARGE, 'amount' => 0],
        ])->keyBy('type');

        DB::table('trees')
            ->select('type', DB::raw('count(\'id\') AS amount'))
            ->where([
                'owner_id' => $user->id,
                'user_id' => null
            ])
            ->groupBy('type')
            ->get()
            ->keyBy('type')
            ->each(function ($treeSummary, $type) use ($availableTrees) {
                $availableTrees[$type] = $treeSummary;
            });

        foreach ($availableTrees as $availableTree) {
            $availableTree->next_available_tree = $availableTree->amount === 0 ? null : DB::table('trees')->where([
                'owner_id' => $user->id,
                'user_id' => null,
                'type' => $availableTree->type,
            ])->first();
        }

        return response()->json($availableTrees);
    }
}
