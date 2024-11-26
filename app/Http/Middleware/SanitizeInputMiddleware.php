<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInputMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sanitized = array_map([$this, 'sanitize'], $request->all());

        $request->merge($sanitized);

        return $next($request);
    }

    /**
     * Sanitize a given value.
     *
     * @param mixed $value
     * @return mixed
     */
    private function sanitize($value)
    { 
        if (is_string($value)) {
            return trim(strip_tags($value));
        }

        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }

        return $value;
    }
}
