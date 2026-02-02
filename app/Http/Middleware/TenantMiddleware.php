<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        // Skip for main domain (e.g., seucrm.com)
        if ($subdomain === 'seucrm' || $subdomain === 'www') {
            return $next($request);
        }

        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        // Set tenant in request for later use
        $request->merge(['tenant' => $tenant]);

        return $next($request);
    }
}