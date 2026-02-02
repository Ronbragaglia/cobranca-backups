<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AdvancedRateLimitMiddleware
{
    public function handle(Request $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        // Verificar se o IP está bloqueado por excesso de tentativas
        if ($this->isRateLimited($key)) {
            AuditLogService::logSecurityEvent('rate_limit_exceeded', [
                'ip' => $request->ip(),
                'key' => $key,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Too many requests. Please try again later.',
                'retry_after' => $this->availableIn($key),
            ], 429);
        }

        // Incrementar contador
        $this->hit($key, $maxAttempts, $decayMinutes);

        // Adicionar headers de rate limiting
        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $this->remaining($key, $maxAttempts)));
        $response->headers->set('X-RateLimit-Reset', $this->availableIn($key));

        return $response;
    }

    protected function resolveRequestSignature(Request $request): string
    {
        // Usar IP + User Agent como chave única
        return sha1(
            $request->ip() .
            '|' . $request->userAgent() .
            '|' . $request->route()->getName()
        );
    }

    protected function isRateLimited(string $key): bool
    {
        return Cache::has("rate_limit:blocked:{$key}");
    }

    protected function hit(string $key, int $maxAttempts, int $decayMinutes): void
    {
        $cacheKey = "rate_limit:{$key}";
        $attempts = Cache::get($cacheKey, 0) + 1;

        if ($attempts > $maxAttempts) {
            // Bloquear por 1 hora
            Cache::put("rate_limit:blocked:{$key}", true, 3600);
        } else {
            // Incrementar tentativas
            Cache::put($cacheKey, $attempts, $decayMinutes * 60);
        }
    }

    protected function remaining(string $key, int $maxAttempts): int
    {
        $attempts = Cache::get("rate_limit:{$key}", 0);
        return max(0, $maxAttempts - $attempts);
    }

    protected function availableIn(string $key): int
    {
        if (!Cache::has("rate_limit:blocked:{$key}")) {
            return 0;
        }

        return Cache::get("rate_limit:blocked:{$key}") - time();
    }
}
