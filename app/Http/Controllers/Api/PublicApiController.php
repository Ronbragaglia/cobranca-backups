<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Cobranca;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PublicApiController extends Controller
{
    public function send(Request $request)
    {
        // Validar API key
        $apiKey = $this->validateApiKey($request);
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired API key'
            ], 401);
        }

        // Verificar rate limiting
        if (!$this->checkRateLimit($apiKey)) {
            return response()->json([
                'success' => false,
                'error' => 'Rate limit exceeded'
            ], 429);
        }

        // Validar requisição
        $validator = Validator::make($request->all(), [
            'numero' => 'required|string',
            'mensagem' => 'required|string|max:1000',
            'descricao' => 'nullable|string|max:255',
            'valor' => 'nullable|numeric|min:0',
            'data_vencimento' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tenant = $apiKey->tenant;
        $subscription = $tenant->subscription;

        // Verificar se o tenant pode enviar mensagens
        if (!$tenant->canSendMessage()) {
            return response()->json([
                'success' => false,
                'error' => 'Message limit exceeded. Please upgrade your plan.'
            ], 403);
        }

        // Criar cobrança
        $cobranca = Cobranca::create([
            'tenant_id' => $tenant->id,
            'descricao' => $request->descricao ?? 'Cobrança via API',
            'valor' => $request->valor ?? 0,
            'status' => 'pendente',
            'data_vencimento' => $request->data_vencimento ?? now()->addDays(7),
            'telefone' => $this->formatPhone($request->numero),
        ]);

        // Enviar mensagem via WhatsApp
        try {
            $this->sendWhatsAppMessage($tenant, $request->numero, $request->mensagem, $cobranca);
            
            // Incrementar contadores
            $tenant->incrementMessages();
            $apiKey->incrementUsage();

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'cobranca_id' => $cobranca->id,
                'status' => $cobranca->status,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCobranca(Request $request, $id)
    {
        $apiKey = $this->validateApiKey($request);
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired API key'
            ], 401);
        }

        $cobranca = Cobranca::where('id', $id)
            ->where('tenant_id', $apiKey->tenant_id)
            ->first();

        if (!$cobranca) {
            return response()->json([
                'success' => false,
                'error' => 'Cobrança not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'cobranca' => [
                'id' => $cobranca->id,
                'descricao' => $cobranca->descricao,
                'valor' => $cobranca->valor,
                'status' => $cobranca->status,
                'data_vencimento' => $cobranca->data_vencimento,
                'telefone' => $cobranca->telefone,
                'created_at' => $cobranca->created_at,
            ]
        ]);
    }

    public function listCobrancas(Request $request)
    {
        $apiKey = $this->validateApiKey($request);
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired API key'
            ], 401);
        }

        $query = Cobranca::where('tenant_id', $apiKey->tenant_id);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        $cobrancas = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'cobrancas' => $cobrancas->items(),
            'pagination' => [
                'total' => $cobrancas->total(),
                'per_page' => $cobrancas->perPage(),
                'current_page' => $cobrancas->currentPage(),
                'last_page' => $cobrancas->lastPage(),
            ]
        ]);
    }

    public function getStats(Request $request)
    {
        $apiKey = $this->validateApiKey($request);
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired API key'
            ], 401);
        }

        $tenant = $apiKey->tenant;
        $subscription = $tenant->subscription;

        $totalCobrancas = Cobranca::where('tenant_id', $tenant->id)->count();
        $cobrancasPagas = Cobranca::where('tenant_id', $tenant->id)->where('status', 'paga')->count();
        $cobrancasPendentes = Cobranca::where('tenant_id', $tenant->id)->where('status', 'pendente')->count();
        $valorTotal = Cobranca::where('tenant_id', $tenant->id)->where('status', 'paga')->sum('valor');

        return response()->json([
            'success' => true,
            'stats' => [
                'total_cobrancas' => $totalCobrancas,
                'cobrancas_pagas' => $cobrancasPagas,
                'cobrancas_pendentes' => $cobrancasPendentes,
                'valor_total' => $valorTotal,
                'messages_used' => $subscription ? $subscription->current_messages_month : 0,
                'messages_limit' => $subscription && $subscription->plan ? 
                    ($subscription->plan->unlimited_messages ? null : $subscription->plan->max_messages_per_month) : 500,
            ]
        ]);
    }

    private function validateApiKey(Request $request): ?ApiKey
    {
        $key = $request->header('X-API-Key') ?? $request->input('api_key');
        
        if (!$key) {
            return null;
        }

        return ApiKey::where('key', $key)
            ->active()
            ->first();
    }

    private function checkRateLimit(ApiKey $apiKey): bool
    {
        $key = "rate_limit:{$apiKey->id}";
        
        $requestsThisMinute = Cache::get($key . ':minute', 0);
        $requestsThisHour = Cache::get($key . ':hour', 0);

        if ($requestsThisMinute >= $apiKey->rate_limit_per_minute) {
            return false;
        }

        if ($requestsThisHour >= $apiKey->rate_limit_per_hour) {
            return false;
        }

        // Incrementar contadores
        Cache::put($key . ':minute', $requestsThisMinute + 1, 60);
        Cache::put($key . ':hour', $requestsThisHour + 1, 3600);

        return true;
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 11 && strpos($phone, '55') !== 0) {
            $phone = '55' . $phone;
        }
        return $phone;
    }

    private function sendWhatsAppMessage(Tenant $tenant, string $numero, string $mensagem, Cobranca $cobranca): void
    {
        // Integrar com Evolution API
        $evolutionService = new \App\Services\EvolutionApiService();
        
        $instanceName = $tenant->evolution_instances[0]['name'] ?? null;
        
        if (!$instanceName) {
            throw new \Exception('No WhatsApp instance configured');
        }

        $evolutionService->sendMessage(
            $instanceName,
            $this->formatPhone($numero),
            $mensagem
        );

        // Atualizar status da cobrança
        $cobranca->update([
            'notificacao_whatsapp_status' => 'enviada',
            'ultimo_envio_whatsapp' => now(),
        ]);
    }
}
