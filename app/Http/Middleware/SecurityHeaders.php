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
        } elseif (
            ! $request->routeIs('home', 'sitemap', 'robots')
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
        ) {
            // SEO owner rule: hanya homepage yang masuk indeks. Halaman publik
            // tetap dapat dirayapi agar seluruh internal link menguatkan beranda.
            $response->headers->set('X-Robots-Tag', 'noindex, follow, noarchive');
        }

        // Basic hardening headers — safe for Laravel Blade + Vite + Bunny Fonts
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('Origin-Agent-Cluster', '?1');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // CSP — relaxed but blocks unsafe execution; allows self, Vite, Bunny Fonts, inline styles/scripts for Blade
        // Note: 'unsafe-inline' needed for Blade inline scripts (theme, Vite) and style attributes
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' https://fonts.bunny.net data:",
            "img-src 'self' data: blob: https:",
            "media-src 'self' blob:",
            "connect-src 'self'",
            "object-src 'none'",
            "frame-src 'none'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        if (! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        if ($request->isSecure() || app()->environment('production')) {
            // Only send HSTS over HTTPS; max-age 1 year, preload disabled by default
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
