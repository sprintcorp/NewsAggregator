<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;

class IsTokenValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract the token from the Authorization header
        $token = $request->bearerToken();

        // Check if token is present
        if (!$token) {
            throw new AuthenticationException('Token not provided.');
        }

        // Attempt to authenticate using Sanctum
        $user = Auth::guard('sanctum')->user();

        // Check if authentication succeeded
        if (!$user) {
            throw new AuthenticationException('Invalid or expired token.');
        }

        // Allow the request to proceed
        return $next($request);
    }
}
