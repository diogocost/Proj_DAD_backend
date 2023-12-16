<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the authenticated user is blocked
        if (auth()->user()->blocked) {
            // The user is blocked, return a response or redirect as needed
            return response()->json(['message' => 'This user is blocked.'], 403); // Forbidden
        }

        // The user is not blocked, allow them to proceed
        return $next($request);
    }
}
