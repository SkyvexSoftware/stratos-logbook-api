<?php

namespace Modules\StratosCore\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Test mirror of Modules\StratosCore\Http\Middleware\StratosAuth, so this
 * module's suite can run without the StratosCore module installed. Keep in
 * sync with the real class in skyvexsoftware/stratos-core-api.
 */
class StratosAuth
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        $model = User::where('api_key', $token)->first();
        if (! is_null($model)) {
            Auth::setUser($model);
            $request->attributes->add(['pilotID' => $model->id]);

            return $next($request);
        } else {
            return response()->json(['success' => false, 'error' => 'Invalid Token'], 401);
        }
    }
}
