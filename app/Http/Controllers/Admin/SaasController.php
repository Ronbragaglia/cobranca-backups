<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Cobranca;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaasController extends Controller
{
    public function dashboard()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::active()->count();
        $totalSubscriptions = Subscription::active()->count();
        $totalRevenue = Subscription::active()
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price');

        // Estatísticas por plano
        $statsByPlan = Plan::withCount(['subscriptions' => function ($query) {
            $query->active();
        }])->get();

        // Últimos tenants
        $recentTenants = Tenant::latest()->take(10)->get();

        // Receita mensal
        $monthlyRevenue = Subscription::active()
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price');

        return view('admin.saas-dashboard', compact(
            'totalTenants',
            'activeTenants',
            'totalSubscriptions',
            'totalRevenue',
            'statsByPlan',
            'recentTenants',
            'monthlyRevenue'
        ));
    }

    public function tenants()
    {
        $tenants = Tenant::with(['subscription', 'subscription.plan'])
            ->latest()
            ->paginate(20);

        return view('admin.tenants', compact('tenants'));
    }

    public function showTenant(Tenant $tenant)
    {
        $tenant->load(['subscription', 'subscription.plan', 'users', 'apiKeys', 'cobrancas']);

        // Estatísticas do tenant
        $totalCobrancas = $tenant->cobrancas()->count();
        $cobrancasPagas = $tenant->cobrancas()->where('status', 'paga')->count();
        $cobrancasPendentes = $tenant->cobrancas()->where('status', 'pendente')->count();
        $cobrancasAtrasadas = $tenant->cobrancas()->where('status', 'atrasada')->count();

        return view('admin.tenant-detail', compact(
            'tenant',
            'totalCobrancas',
            'cobrancasPagas',
            'cobrancasPendentes',
            'cobrancasAtrasadas'
        ));
    }

    public function updateTenant(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean',
            'max_whatsapp_instances' => 'integer|min:0',
            'max_messages_per_month' => 'integer|min:0',
        ]);

        $tenant->update($validated);

        return back()->with('success', 'Tenant atualizado com sucesso!');
    }

    public function deactivateTenant(Tenant $tenant)
    {
        $tenant->update(['active' => false]);

        // Desativar todas as API keys
        $tenant->apiKeys()->update(['active' => false]);

        return back()->with('success', 'Tenant desativado com sucesso!');
    }

    public function activateTenant(Tenant $tenant)
    {
        $tenant->update(['active' => true]);

        return back()->with('success', 'Tenant ativado com sucesso!');
    }

    public function subscriptions()
    {
        $subscriptions = Subscription::with(['tenant', 'plan'])
            ->latest()
            ->paginate(20);

        return view('admin.subscriptions', compact('subscriptions'));
    }

    public function showSubscription(Subscription $subscription)
    {
        $subscription->load(['tenant', 'plan']);

        return view('admin.subscription-detail', compact('subscription'));
    }

    public function plans()
    {
        $plans = Plan::ordered()->get();

        return view('admin.plans', compact('plans'));
    }

    public function createPlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'interval' => 'required|integer|min:1',
            'max_whatsapp_instances' => 'required|integer|min:0',
            'max_messages_per_month' => 'required|integer|min:0',
            'unlimited_messages' => 'boolean',
            'api_access' => 'boolean',
            'custom_qr' => 'boolean',
            'analytics' => 'boolean',
            'priority_support' => 'boolean',
            'features' => 'array',
            'active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        Plan::create($validated);

        return back()->with('success', 'Plano criado com sucesso!');
    }

    public function updatePlan(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'interval' => 'required|integer|min:1',
            'max_whatsapp_instances' => 'required|integer|min:0',
            'max_messages_per_month' => 'required|integer|min:0',
            'unlimited_messages' => 'boolean',
            'api_access' => 'boolean',
            'custom_qr' => 'boolean',
            'analytics' => 'boolean',
            'priority_support' => 'boolean',
            'features' => 'array',
            'active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $plan->update($validated);

        return back()->with('success', 'Plano atualizado com sucesso!');
    }

    public function deletePlan(Plan $plan)
    {
        // Verificar se existem assinaturas ativas
        $activeSubscriptions = $plan->subscriptions()->active()->count();

        if ($activeSubscriptions > 0) {
            return back()->with('error', 'Não é possível excluir um plano com assinaturas ativas.');
        }

        $plan->delete();

        return back()->with('success', 'Plano excluído com sucesso!');
    }

    public function apiKeys()
    {
        $apiKeys = ApiKey::with(['tenant', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.api-keys', compact('apiKeys'));
    }

    public function revokeApiKey(ApiKey $apiKey)
    {
        $apiKey->update(['active' => false]);

        return back()->with('success', 'API Key revogada com sucesso!');
    }

    public function analytics()
    {
        // Estatísticas gerais
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::active()->count();
        $totalSubscriptions = Subscription::count();
        $activeSubscriptions = Subscription::active()->count();

        // Cobranças
        $totalCobrancas = Cobranca::count();
        $cobrancasPagas = Cobranca::where('status', 'paga')->count();
        $cobrancasPendentes = Cobranca::where('status', 'pendente')->count();
        $cobrancasAtrasadas = Cobranca::where('status', 'atrasada')->count();

        // Receita
        $monthlyRevenue = Subscription::active()
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price');

        $totalRevenue = Subscription::active()
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price') * 12; // Estimativa anual

        // Distribuição por plano
        $distributionByPlan = Plan::withCount(['subscriptions' => function ($query) {
            $query->active();
        }])->get();

        // Crescimento mensal
        $monthlyGrowth = Tenant::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.saas-analytics', compact(
            'totalTenants',
            'activeTenants',
            'totalSubscriptions',
            'activeSubscriptions',
            'totalCobrancas',
            'cobrancasPagas',
            'cobrancasPendentes',
            'cobrancasAtrasadas',
            'monthlyRevenue',
            'totalRevenue',
            'distributionByPlan',
            'monthlyGrowth'
        ));
    }

    public function createTenant(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:255|unique:tenants,subdomain',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'plan_id' => 'required|exists:plans,id',
        ]);

        // Criar tenant
        $tenant = Tenant::create([
            'name' => $validated['name'],
            'subdomain' => $validated['subdomain'],
            'stripe_customer_id' => null,
            'subscription_status' => 'active',
            'evolution_api_key' => "evolution_api_key_{$validated['subdomain']}",
            'evolution_api_url' => "https://api.evolution.com/instance/{$validated['subdomain']}",
            'evolution_instances' => [],
            'qr_code_image' => null,
            'custom_qr_enabled' => false,
            'max_whatsapp_instances' => 1,
            'max_messages_per_month' => 500,
            'current_messages_month' => 0,
            'usage_reset_at' => now()->startOfMonth(),
            'active' => true,
            'trial_ends_at' => null,
        ]);

        // Criar usuário
        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'tenant_id' => $tenant->id,
        ]);

        // Criar assinatura
        $plan = Plan::find($validated['plan_id']);
        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'stripe_subscription_id' => null,
            'stripe_customer_id' => null,
            'stripe_status' => 'active',
            'status' => 'active',
            'trial_ends_at' => null,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'canceled_at' => null,
            'current_messages_month' => 0,
            'current_whatsapp_instances' => 0,
            'usage_reset_at' => now()->startOfMonth(),
        ]);

        return back()->with('success', 'Tenant criado com sucesso!');
    }
}
