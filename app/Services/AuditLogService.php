<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public static function log(string $action, array $data = []): AuditLog
    {
        $request = request();
        
        return AuditLog::create([
            'tenant_id' => Auth::check() && Auth::user()->tenant_id ? Auth::user()->tenant_id : null,
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $data['model_type'] ?? null,
            'model_id' => $data['model_id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'success' => $data['success'] ?? true,
            'error_message' => $data['error_message'] ?? null,
        ]);
    }

    public static function logLogin(bool $success, ?string $error = null): AuditLog
    {
        return self::log('login', [
            'success' => $success,
            'error_message' => $error,
            'metadata' => [
                'email' => request()->input('email'),
            ],
        ]);
    }

    public static function logLogout(): AuditLog
    {
        return self::log('logout');
    }

    public static function logApiRequest(string $action, bool $success, ?string $error = null): AuditLog
    {
        return self::log('api_' . $action, [
            'success' => $success,
            'error_message' => $error,
            'metadata' => [
                'api_key' => substr(request()->header('X-API-Key') ?? '', 0, 10) . '...',
            ],
        ]);
    }

    public static function logModelChange(string $action, string $modelType, $modelId, array $oldValues = [], array $newValues = []): AuditLog
    {
        return self::log($action, [
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    public static function logSecurityEvent(string $event, array $metadata = []): AuditLog
    {
        return self::log('security_' . $event, [
            'metadata' => $metadata,
        ]);
    }

    public static function logSubscriptionChange(string $action, array $metadata = []): AuditLog
    {
        return self::log('subscription_' . $action, [
            'metadata' => $metadata,
        ]);
    }

    public static function logApiKeyAction(string $action, string $apiKeyId, array $metadata = []): AuditLog
    {
        return self::log('api_key_' . $action, [
            'model_type' => 'ApiKey',
            'model_id' => $apiKeyId,
            'metadata' => $metadata,
        ]);
    }

    public static function logTenantAction(string $action, int $tenantId, array $metadata = []): AuditLog
    {
        return self::log('tenant_' . $action, [
            'model_type' => 'Tenant',
            'model_id' => $tenantId,
            'metadata' => $metadata,
        ]);
    }

    public static function logFailedAttempt(string $type, array $metadata = []): AuditLog
    {
        return self::log('failed_attempt_' . $type, [
            'success' => false,
            'metadata' => $metadata,
        ]);
    }
}
