<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class EmailVerified
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
        if (Auth::user() && !Auth::user()->email_verified) {
            abort(Response::HTTP_FORBIDDEN, trans('errors.User email not verified'));
        }

        return $next($request);
    }
}
