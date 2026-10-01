<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $isPrivateOrUtility = $request->is(
            'admin', 'admin/*', 'portal', 'portal/*', 'health', 'up', 'webhooks/*'
        );
        if ($isPrivateOrUtility || $response->getStatusCode() >= 400) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
        }

        // Basic hardening headers — safe for Laravel Blade + Vite + Bunny Fonts
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // CSP — relaxed but blocks unsafe execution; allows self, Vite, Bunny Fonts, inline styles/scripts for Blade
        // Note: 'unsafe-inline' needed for Blade inline scripts (theme, Vite) and style attributes
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://fonts.bunny.net https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.bunny.net data:",
            "img-src 'self' data: blob: http: https:",
            "connect-src 'self' ws: wss: http: https:",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        if ($request->isSecure() || app()->environment('production')) {
            // Only send HSTS over HTTPS; max-age 1 year, preload disabled by default
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
