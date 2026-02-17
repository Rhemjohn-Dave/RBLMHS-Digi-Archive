<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFaculty
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isFaculty()) {
            return response()->json(['message' => 'Unauthorized. Faculty only.'], 403);
        }
        return $next($request);
    }
}
