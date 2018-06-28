<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

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
        if ($request->user && !$request->user->email_verified) {
            abort(Response::HTTP_FORBIDDEN, trans('errors.User email not verified'));
        }

        return $next($request);
    }
}
