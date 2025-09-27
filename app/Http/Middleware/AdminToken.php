<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = env('ADMIN_TOKEN');
        if (!$token) {
            abort(403, 'Admin token not configured');
        }

        $provided = $request->header('X-Admin-Token') ?? $request->query('token');
        if (!hash_equals($token, (string) $provided)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}

