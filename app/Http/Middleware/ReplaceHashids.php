<?php

namespace App\Http\Middleware;

use App\HashidsTransformer;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Vinkla\Hashids\Facades\Hashids;

class ReplaceHashids
{
    public static $shouldReplace = [
        'user_id',
        'owner_id',
        'upline_id',
        'operator_id',
        'child_account_id', /*'operatable_id',*/
    ];

    public $transformer;

    public function __construct(HashidsTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        foreach (static::$shouldReplace as $shouldReplaced) {
            if ($request->has($shouldReplaced)) {
                $request->merge([
                    $shouldReplaced => app(HashidsTransformer::class)->decode($request->{$shouldReplaced})
                ]);

                if (!$request->{$shouldReplaced}) {
                    abort(
                        Response::HTTP_BAD_REQUEST,
                        trans('errors.Bad Hashids')
                    );
                }
            }
        }

        $response = $next($request);

        $this->transformer->transform($response->original);

        if ($response instanceof JsonResponse) {
            $response->setData($response->original);
        } else {
            $response->setContent($response->original);
        }

        return $response;
    }
}
