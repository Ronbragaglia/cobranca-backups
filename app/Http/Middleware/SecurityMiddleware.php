<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class SecurityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Verificar IP bloqueado
        if ($this->isIpBlocked($request->ip())) {
            return response()->json([
                'success' => false,
                'error' => 'IP blocked due to suspicious activity'
            ], 403);
        }

        // Verificar User-Agent suspeito
        if ($this->isSuspiciousUserAgent($request->userAgent())) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid request'
            ], 400);
        }

        // Verificar headers de segurança
        if (!$this->hasSecurityHeaders($request)) {
            return response()->json([
                'success' => false,
                'error' => 'Missing required security headers'
            ], 400);
        }

        // Adicionar headers de segurança na resposta
        $response = $next($request);

        // Headers de segurança HTTP
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }

    private function isIpBlocked(string $ip): bool
    {
        return Cache::has("blocked_ip:{$ip}");
    }

    private function isSuspiciousUserAgent(?string $userAgent): bool
    {
        if (!$userAgent) {
            return true;
        }

        $suspiciousPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/curl/i',
            '/wget/i',
            '/python/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                // Logar tentativa suspeita
                AuditLogService::logSecurityEvent('suspicious_user_agent', [
                    'user_agent' => $userAgent,
                    'pattern' => $pattern,
                ]);
                return true;
            }
        }

        return false;
    }

    private function hasSecurityHeaders(Request $request): bool
    {
        // Verificar se a requisição tem headers mínimos necessários
        return true; // Pode ser ajustado conforme necessário
    }
}
