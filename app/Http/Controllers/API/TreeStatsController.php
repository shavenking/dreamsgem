<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Tree;
use App\User;

class TreeStatsController extends Controller
{
    public function index(User $user)
    {
        $this->authorize('getTreeStats', $user);

        $activatedChildrenCount = $user->children->filter(function (User $child) {
            return $child->activated;
        })->count();

        $nLevel = [
            0 => 0,
            1 => 5,
            2 => 8,
            3 => 10,
            4 => 10,
            5 => 10,
            6 => 10,
            7 => 10,
        ][$activatedChildrenCount];

        $children = collect();
        $candidateChildren = collect($user->children);

        while ($nLevel-- > 0) {
            $oneLevelDownChildren = collect();

            while ($child = $candidateChildren->shift()) {
                $oneLevelDownChildren = $oneLevelDownChildren->concat($child->children);
                $children->push($child);
            }

            $candidateChildren = collect($oneLevelDownChildren);
        }

        $children = $children->filter(function ($user) {
            return $user->activated;
        });

        $totalUserCount = $children->count();
        $userIds = $children->push($user)->pluck('id');

        $totalRemain = Tree::whereIn('user_id', $userIds)->sum('remain');
        $totalCapacity = Tree::whereIn('user_id', $userIds)->sum('capacity');

        return response()->json([
            'total_user_count' => $totalUserCount,
            'total_remain' => $totalRemain,
            'total_capacity' => $totalCapacity,
        ]);
    }
}
