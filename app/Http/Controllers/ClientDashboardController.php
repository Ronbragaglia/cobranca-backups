<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\Cobranca;
use App\Models\MessageTemplate;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $tenant = Auth::user()->tenant;
        $subscription = $tenant->subscription;
        $plan = $subscription ? $subscription->plan : null;

        // Estatísticas
        $totalCobrancas = Cobranca::where('tenant_id', $tenant->id)->count();
        $cobrancasPagas = Cobranca::where('tenant_id', $tenant->id)->where('status', 'paga')->count();
        $cobrancasPendentes = Cobranca::where('tenant_id', $tenant->id)->where('status', 'pendente')->count();
        $cobrancasAtrasadas = Cobranca::where('tenant_id', $tenant->id)->where('status', 'atrasada')->count();

        // Uso de mensagens
        $messagesUsed = $subscription ? $subscription->current_messages_month : 0;
        $messagesLimit = $plan ? ($plan->unlimited_messages ? null : $plan->max_messages_per_month) : 500;

        // Instâncias WhatsApp
        $instancesUsed = $subscription ? $subscription->current_whatsapp_instances : 0;
        $instancesLimit = $plan ? $plan->max_whatsapp_instances : 1;

        return view('client.dashboard', compact(
            'tenant',
            'subscription',
            'plan',
            'totalCobrancas',
            'cobrancasPagas',
            'cobrancasPendentes',
            'cobrancasAtrasadas',
            'messagesUsed',
            'messagesLimit',
            'instancesUsed',
            'instancesLimit'
        ));
    }

    public function settings()
    {
        $tenant = Auth::user()->tenant;
        $subscription = $tenant->subscription;
        $plan = $subscription ? $subscription->plan : null;

        return view('client.settings', compact('tenant', 'subscription', 'plan'));
    }

    public function updateSettings(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'evolution_api_url' => 'nullable|url',
        ]);

        $tenant->update($validated);

        return back()->with('success', 'Configurações atualizadas com sucesso!');
    }

    public function apiKeys()
    {
        $tenant = Auth::user()->tenant;
        $apiKeys = $tenant->apiKeys()->latest()->get();

        return view('client.api-keys', compact('apiKeys'));
    }

    public function createApiKey(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $apiKey = ApiKey::create([
            'tenant_id' => Auth::user()->tenant_id,
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'key' => ApiKey::generateKey(),
            'prefix' => null,
            'abilities' => ['*'],
            'rate_limit_per_minute' => 60,
            'rate_limit_per_hour' => 1000,
            'total_requests' => 0,
            'last_used_at' => null,
            'active' => true,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return back()->with('success', 'API Key criada com sucesso! Salve a chave: ' . $apiKey->key);
    }

    public function deleteApiKey(ApiKey $apiKey)
    {
        if ($apiKey->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $apiKey->delete();

        return back()->with('success', 'API Key removida com sucesso!');
    }

    public function templates()
    {
        $tenant = Auth::user()->tenant;
        $templates = $tenant->messageTemplates()->latest()->get();

        return view('client.templates', compact('templates'));
    }

    public function createTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:cobranca,lembrete,agradecimento,custom',
            'content' => 'required|string',
        ]);

        $template = MessageTemplate::create([
            'tenant_id' => Auth::user()->tenant_id,
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'variables' => $this->extractVariables($validated['content']),
            'is_default' => false,
            'active' => true,
            'usage_count' => 0,
            'last_used_at' => null,
        ]);

        return back()->with('success', 'Template criado com sucesso!');
    }

    public function updateTemplate(Request $request, MessageTemplate $template)
    {
        if ($template->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:cobranca,lembrete,agradecimento,custom',
            'content' => 'required|string',
            'active' => 'boolean',
        ]);

        $template->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'variables' => $this->extractVariables($validated['content']),
            'active' => $validated['active'] ?? true,
        ]);

        return back()->with('success', 'Template atualizado com sucesso!');
    }

    public function deleteTemplate(MessageTemplate $template)
    {
        if ($template->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $template->delete();

        return back()->with('success', 'Template removido com sucesso!');
    }

    public function uploadCsv(Request $request)
    {
        $validated = $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $path = $file->store('csv-uploads', 'local');

        // Processar CSV
        $csvData = $this->parseCsv($path);

        // Criar cobranças em lote
        $created = 0;
        foreach ($csvData as $row) {
            if (isset($row['telefone']) && isset($row['valor'])) {
                Cobranca::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'descricao' => $row['descricao'] ?? 'Cobrança importada',
                    'valor' => $this->parseCurrency($row['valor']),
                    'status' => 'pendente',
                    'data_vencimento' => $row['data_vencimento'] ?? now()->addDays(7),
                    'telefone' => $this->formatPhone($row['telefone']),
                ]);
                $created++;
            }
        }

        // Remover arquivo processado
        Storage::disk('local')->delete($path);

        return back()->with('success', "Importação concluída! {$created} cobranças criadas.");
    }

    public function qrCode()
    {
        $tenant = Auth::user()->tenant;
        $plan = $tenant->subscription ? $tenant->subscription->plan : null;

        if (!$plan || !$plan->custom_qr) {
            return back()->with('error', 'QR personalizado não disponível no seu plano.');
        }

        return view('client.qr-code', compact('tenant'));
    }

    public function uploadQrCode(Request $request)
    {
        $tenant = Auth::user()->tenant;
        $plan = $tenant->subscription ? $tenant->subscription->plan : null;

        if (!$plan || !$plan->custom_qr) {
            return back()->with('error', 'QR personalizado não disponível no seu plano.');
        }

        $validated = $request->validate([
            'qr_code' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $path = $request->file('qr_code')->store('qr-codes', 'public');
        $tenant->update(['qr_code_image' => $path]);

        return back()->with('success', 'QR Code atualizado com sucesso!');
    }

    public function analytics()
    {
        $tenant = Auth::user()->tenant;
        $plan = $tenant->subscription ? $tenant->subscription->plan : null;

        if (!$plan || !$plan->analytics) {
            return back()->with('error', 'Analytics não disponível no seu plano.');
        }

        // Dados para analytics
        $cobrancasPorStatus = Cobranca::where('tenant_id', $tenant->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $cobrancasUltimos30Dias = Cobranca::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $valorTotalCobrado = Cobranca::where('tenant_id', $tenant->id)
            ->where('status', 'paga')
            ->sum('valor');

        return view('client.analytics', compact(
            'tenant',
            'cobrancasPorStatus',
            'cobrancasUltimos30Dias',
            'valorTotalCobrado'
        ));
    }

    private function extractVariables(string $content): array
    {
        preg_match_all('/\{([^}]+)\}/', $content, $matches);
        return $matches[1] ?? [];
    }

    private function parseCsv(string $path): array
    {
        $file = fopen(Storage::disk('local')->path($path), 'r');
        $headers = fgetcsv($file);
        $data = [];

        while (($row = fgetcsv($file)) !== false) {
            $data[] = array_combine($headers, $row);
        }

        fclose($file);
        return $data;
    }

    private function parseCurrency(string $value): float
    {
        $value = preg_replace('/[^0-9,.]/', '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return (float) $value;
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 11 && strpos($phone, '55') !== 0) {
            $phone = '55' . $phone;
        }
        return $phone;
    }
}
