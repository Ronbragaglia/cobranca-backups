<?php

namespace App\Http\Controllers;

use App\Services\MultiInstanceEvolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MultiInstanceController extends Controller
{
    protected MultiInstanceEvolutionService $multiInstance;

    public function __construct(MultiInstanceEvolutionService $multiInstance)
    {
        $this->multiInstance = $multiInstance;
    }

    /**
     * Listar todas as instâncias
     */
    public function index()
    {
        $instances = $this->multiInstance->getAllInstances();

        return response()->json([
            'success' => true,
            'instances' => $instances,
        ]);
    }

    /**
     * Obter status de todas as instâncias
     */
    public function status()
    {
        $statuses = $this->multiInstance->getAllInstancesStatus();

        return response()->json([
            'success' => true,
            'statuses' => $statuses,
            'all_connected' => $this->multiInstance->areAllInstancesConnected(),
        ]);
    }

    /**
     * Obter estatísticas de todas as instâncias
     */
    public function stats()
    {
        $stats = $this->multiInstance->getAllInstancesStats();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'total_messages_per_minute' => collect($stats)->sum('messages_per_minute'),
            'total_messages' => collect($stats)->sum('total_messages'),
        ]);
    }

    /**
     * Obter instância com menor carga (load balancing)
     */
    public function leastLoaded()
    {
        $instance = $this->multiInstance->getLeastLoadedInstance();

        return response()->json([
            'success' => true,
            'instance' => $instance,
        ]);
    }

    /**
     * Criar nova instância
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'instanceName' => 'required|string|max:50',
            'tenant_id' => 'required|integer|min:1',
            'priority' => 'integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->multiInstance->createInstance(
            $request->input('instanceName'),
            $request->input('tenant_id'),
            $request->input('priority', 1)
        );

        return response()->json($result);
    }

    /**
     * Deletar instância
     */
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'instanceName' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->multiInstance->deleteInstance($request->input('instanceName'));

        return response()->json($result);
    }

    /**
     * Conectar instância (escanear QR)
     */
    public function connect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'instanceName' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->multiInstance->connectInstance($request->input('instanceName'));

        return response()->json($result);
    }

    /**
     * Enviar mensagem
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|string',
            'text' => 'required|string',
            'tenant_id' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->multiInstance->sendMessage(
            $request->input('number'),
            $request->input('text'),
            $request->input('tenant_id')
        );

        return response()->json($result);
    }

    /**
     * Enviar mensagem de cobrança
     */
    public function sendCobranca(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|string',
            'nome' => 'required|string',
            'valor' => 'required|numeric|min:0',
            'data_vencimento' => 'required|date',
            'link_pagamento' => 'nullable|url',
            'tenant_id' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->multiInstance->sendCobrancaMessage(
            $request->input('number'),
            $request->input('nome'),
            number_format($request->input('valor'), 2, ',', '.'),
            $request->input('data_vencimento'),
            $request->input('link_pagamento'),
            $request->input('tenant_id')
        );

        return response()->json($result);
    }

    /**
     * Enviar lembrete de cobrança
     */
    public function sendReminder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|string',
            'nome' => 'required|string',
            'valor' => 'required|numeric|min:0',
            'data_vencimento' => 'required|date',
            'tenant_id' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->multiInstance->sendReminderMessage(
            $request->input('number'),
            $request->input('nome'),
            number_format($request->input('valor'), 2, ',', '.'),
            $request->input('data_vencimento'),
            $request->input('tenant_id')
        );

        return response()->json($result);
    }

    /**
     * Enviar confirmação de pagamento
     */
    public function sendPaymentConfirmation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|string',
            'nome' => 'required|string',
            'valor' => 'required|numeric|min:0',
            'tenant_id' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->multiInstance->sendPaymentConfirmation(
            $request->input('number'),
            $request->input('nome'),
            number_format($request->input('valor'), 2, ',', '.'),
            $request->input('tenant_id')
        );

        return response()->json($result);
    }

    /**
     * Upload CSV de clientes
     */
    public function uploadClients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt',
            'tenant_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');
        $tenantId = $request->input('tenant_id');

        // Ler CSV
        $path = $file->storeAs('clients', 'csv');
        $handle = fopen(storage_path('app/' . $path), 'r');

        $clients = [];
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if ($row > 0) { // Pular cabeçalho
                $clients[] = [
                    'nome' => $data[0] ?? '',
                    'telefone' => $data[1] ?? '',
                    'valor' => $data[2] ?? '',
                    'data_vencimento' => $data[3] ?? '',
                ];
            }
            $row++;
        }

        fclose($handle);

        // Processar clientes (enviar mensagens)
        $enviadas = 0;
        $erros = 0;

        foreach ($clients as $client) {
            $result = $this->multiInstance->sendCobrancaMessage(
                $client['telefone'],
                $client['nome'],
                $client['valor'],
                $client['data_vencimento'],
                null,
                $tenantId
            );

            if ($result['success']) {
                $enviadas++;
            } else {
                $erros++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "CSV processado",
            'stats' => [
                'total_clients' => count($clients),
                'enviadas' => $enviadas,
                'erros' => $erros,
            ],
        ]);
    }

    /**
     * Obter relatório de clientes
     */
    public function clientsReport()
    {
        // Buscar clientes do banco de dados
        $clients = \App\Models\Cobranca::select('nome', 'telefone', 'valor', 'data_vencimento', 'status')
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get();

        return response()->json([
            'success' => true,
            'clients' => $clients,
        ]);
    }

    /**
     * Obter métricas de uso
     */
    public function usageMetrics()
    {
        $stats = $this->multiInstance->getAllInstancesStats();

        return response()->json([
            'success' => true,
            'metrics' => $stats,
            'capacity' => [
                'total_instances' => count($stats),
                'total_capacity_per_minute' => count($stats) * 500, // 500 msg/min por instância
                'current_usage_per_minute' => collect($stats)->sum('messages_per_minute'),
                'utilization_percent' => round((collect($stats)->sum('messages_per_minute') / (count($stats) * 500)) * 100, 2),
            ],
        ]);
    }

    /**
     * Obter preços por tenant
     */
    public function pricing()
    {
        // Preços por plano
        $pricing = [
            [
                'name' => 'Básico',
                'price' => 97.00,
                'whatsapp_instances' => 1,
                'messages_per_month' => 5000,
                'features' => ['Envio de cobranças', 'Lembretes automáticos', 'Dashboard básico'],
            ],
            [
                'name' => 'Profissional',
                'price' => 197.00,
                'whatsapp_instances' => 3,
                'messages_per_month' => 15000,
                'features' => ['Envio de cobranças', 'Lembretes automáticos', 'Dashboard avançado', 'API REST', 'Suporte prioritário'],
            ],
            [
                'name' => 'Empresarial',
                'price' => 497.00,
                'whatsapp_instances' => 5,
                'messages_per_month' => 25000,
                'features' => ['Envio de cobranças', 'Lembretes automáticos', 'Dashboard avançado', 'API REST completa', 'Suporte 24/7', 'Load balancing', 'HAProxy'],
            ],
            [
                'name' => 'Personalizado',
                'price' => 997.00,
                'whatsapp_instances' => 10,
                'messages_per_month' => 50000,
                'features' => ['Envio de cobranças', 'Lembretes automáticos', 'Dashboard avançado', 'API REST completa', 'Suporte 24/7', 'Load balancing', 'HAProxy', 'SLA 99.99%'],
            ],
        ];

        return response()->json([
            'success' => true,
            'pricing' => $pricing,
        ]);
    }

    /**
     * Criar novo tenant
     */
    public function createTenant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:tenants',
            'plan' => 'required|in:basico,profissional,empresarial,personalizado',
            'max_instances' => 'integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Criar tenant
        $tenant = \App\Models\Tenant::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'plan' => $request->input('plan'),
            'max_instances' => $request->input('max_instances'),
            'status' => 'ativo',
        ]);

        // Criar instâncias padrão para o tenant
        $maxInstances = $request->input('max_instances');
        for ($i = 1; $i <= min($maxInstances, 5); $i++) {
            $instanceName = "cobranca{$tenant->id}-{$i}";

            $this->multiInstance->createInstance(
                $instanceName,
                $tenant->id,
                $i
            );
        }

        return response()->json([
            'success' => true,
            'tenant' => $tenant,
            'message' => "Tenant e instâncias criadas com sucesso",
        ]);
    }

    /**
     * Obter tenants
     */
    public function tenants()
    {
        $tenants = \App\Models\Tenant::withCount('cobrancas')->get();

        return response()->json([
            'success' => true,
            'tenants' => $tenants,
        ]);
    }

    /**
     * Obter dashboard SaaS
     */
    public function saasDashboard()
    {
        $tenants = \App\Models\Tenant::withCount('cobrancas')->get();
        $instancesStats = $this->multiInstance->getAllInstancesStats();

        $totalMessages = collect($instancesStats)->sum('total_messages');
        $totalMessagesPerMinute = collect($instancesStats)->sum('messages_per_minute');

        return response()->json([
            'success' => true,
            'dashboard' => [
                'tenants' => $tenants,
                'total_tenants' => count($tenants),
                'total_instances' => count($instancesStats),
                'total_messages' => $totalMessages,
                'messages_per_minute' => $totalMessagesPerMinute,
                'capacity_utilization' => round(($totalMessagesPerMinute / (count($instancesStats) * 500)) * 100, 2),
            ],
        ]);
    }
}
