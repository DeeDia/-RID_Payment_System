<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * SecurityHeaders — attaches all security-related HTTP response headers.
 *
 * Mitigates: Clickjacking (X-Frame-Options, CSP frame-ancestors),
 *            XSS (CSP, X-XSS-Protection), MIME sniffing, MITM (HSTS).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HTTP Strict Transport Security — 1 year, include subdomains, preload eligible
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains; preload'
        );

        // Content Security Policy
        // script-src 'self' only — no inline scripts, no eval()
        // frame-ancestors 'none' — blocks all iframe embedding (clickjacking)
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self' 'unsafe-inline'",   // Tailwind/Vite builds inline styles; tighten in prod with nonce
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
