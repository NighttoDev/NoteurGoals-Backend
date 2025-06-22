<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',           // Exclude all API routes
        '/api/*',          // Alternative format
        'sanctum/*',       // Exclude Sanctum routes
        '/sanctum/*',      // Alternative format
    ];

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     */
    protected function inExceptArray($request): bool
    {
        // First check parent implementation
        if (parent::inExceptArray($request)) {
            return true;
        }

        // Additional check for API routes
        $uri = $request->getRequestUri();
        
        // Check if request is for API endpoints
        if (str_starts_with($uri, '/api/') || str_starts_with($uri, 'api/')) {
            return true;
        }

        // Check if request has API content type
        if ($request->expectsJson() || $request->is('api/*')) {
            return true;
        }

        return false;
    }
} 