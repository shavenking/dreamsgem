<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Support\Carbon;

class CheckForMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $now = Carbon::now();
        $from = Carbon::createFromTime(16); // 24:00 Asia/Taipei
        $to = $from->copy()->addHours(9); // 09:00 Asia/Taipei

        if ($now->between($from, $to)) {
            throw new MaintenanceModeException($from->timestamp, $now->diffInSeconds($from));
        }

        return $next($request);
    }
}
