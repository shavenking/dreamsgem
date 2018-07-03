<?php

namespace App\Http\Controllers\API;

use App\Dragon;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DragonSummaryController extends Controller
{
    public function index(User $user)
    {
        $availableDragons = collect([
            (object) ['type' => Dragon::TYPE_NORMAL, 'amount' => 0],
            (object) ['type' => Dragon::TYPE_SMALL, 'amount' => 0],
        ])->keyBy('type');

        DB::table('dragons')
            ->select('type', DB::raw('count(\'id\') AS amount'))
            ->where([
                'owner_id' => $user->id,
                'user_id' => null
            ])
            ->groupBy('type')
            ->get()
            ->keyBy('type')
            ->each(function ($dragonSummary, $type) use ($availableDragons) {
                $availableDragons[$type] = $dragonSummary;
            });

        foreach ($availableDragons as $availableDragon) {
            $availableDragon->next_available_dragon = $availableDragon->amount === 0 ? null : DB::table('dragons')->where([
                'owner_id' => $user->id,
                'user_id' => null,
                'type' => $availableDragon->type,
            ])->first();
        }

        return response()->json($availableDragons);
    }
}
