<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
                'data' => null,
            ], 401);
        }

        if (empty($roles) || !$user->hasAnyRole($roles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
