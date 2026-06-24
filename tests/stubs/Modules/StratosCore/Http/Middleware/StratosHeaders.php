<?php

namespace Modules\StratosCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Test mirror of Modules\StratosCore\Http\Middleware\StratosHeaders, so this
 * module's suite can run without the StratosCore module installed. Keep in
 * sync with the real class in skyvexsoftware/stratos-core-api.
 */
class StratosHeaders
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $method = $request->method();
        if ($method === 'OPTIONS' || $method === 'HEAD') {
            $response = response()->json(null);
        } else {
            $response = $next($request);
        }
        $response->withHeaders([
            'Content-type' => 'application/json',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, HEAD',
            'Access-Control-Allow-Headers' => 'Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-Stratos-Version, User-Agent',
            'Access-Control-Allow-Origin' => '*',
        ]);

        return $response;
    }
}
