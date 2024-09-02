<?php

namespace App\Http\Middleware;

/**
 * Despite we are using the default Laravel CORS middleware, we need to enforce CORS headers on all responses.
 * The default Laravel CORS middleware sends the headers only when the request is preflighted.
 * With this middleware we also gain more control over the CORS policy.
 *
 * Class EnforceCors
 * @package App\Http\Middleware
 */
class EnforceCors
{
    public function handle($request, $next)
    {
        $response = $next($request);

        $response->header('Access-Control-Allow-Origin', implode(',', config('cors.allowed_origins', ['*'])));
        $response->header('Access-Control-Allow-Methods', implode(',', config('cors.allowed_methods', ['*'])));
        $response->header('Access-Control-Allow-Credentials', config('cors.supports_credentials', true) ? 'true' : 'false');
        $response->header('Access-Control-Allow-Headers', implode(',', config('cors.allowed_headers', ['*'])));

        return $response;
    }
}
