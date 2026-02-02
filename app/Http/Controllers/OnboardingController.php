<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantSettings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class OnboardingController extends Controller
{
    /**
     * Mostrar o passo 1: Dados da empresa
     */
    public function step1(Request $request)
    {
        // Se já completou onboarding, redirecionar para o painel
        if (Auth::check() && Auth::user()->tenant_id) {
            return redirect()->route('painel.index');
        }

        return view('onboarding.step1');
    }

    /**
     * Processar passo 1 e ir para passo 2
     */
    public function storeStep1(Request $request)
    {
        $validated = $request->validate([
            'empresa_nome' => 'required|string|max:255',
            'empresa_subdominio' => 'required|string|max:50|regex:/^[a-z0-9-]+$/|unique:tenants,subdomain',
        ]);

        // Salvar na sessão
        session(['onboarding_step1' => $validated]);

        return redirect()->route('onboarding.step2');
    }

    /**
     * Mostrar o passo 2: Dados do responsável
     */
    public function step2(Request $request)
    {
        if (!session('onboarding_step1')) {
            return redirect()->route('onboarding.step1');
        }

        return view('onboarding.step2');
    }

    /**
     * Processar passo 2 e ir para passo 3
     */
    public function storeStep2(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'telefone' => 'required|string|max:20',
        ]);

        // Salvar na sessão
        session(['onboarding_step2' => $validated]);

        return redirect()->route('onboarding.step3');
    }

    /**
     * Mostrar o passo 3: Preferências de cobrança
     */
    public function step3(Request $request)
    {
        if (!session('onboarding_step1') || !session('onboarding_step2')) {
            return redirect()->route('onboarding.step1');
        }

        return view('onboarding.step3');
    }

    /**
     * Processar passo 3 e finalizar onboarding
     */
    public function storeStep3(Request $request)
    {
        $validated = $request->validate([
            'moeda' => 'required|string|size:3',
            'dias_vencimento' => 'required|integer|min:1|max:90',
            'lembretes_whatsapp' => 'required|boolean',
            'lembretes_dias_antes' => 'nullable|array',
            'lembretes_dias_antes.*' => 'integer|min:1|max:30',
            'lembrete_dia_vencimento' => 'required|boolean',
            'lembretes_dias_depois' => 'nullable|array',
            'lembretes_dias_depois.*' => 'integer|min:1|max:30',
        ]);

        // Recuperar dados dos passos anteriores
        $step1 = session('onboarding_step1');
        $step2 = session('onboarding_step2');

        // Criar tenant
        $tenant = Tenant::create([
            'name' => $step1['empresa_nome'],
            'subdomain' => $step1['empresa_subdominio'],
            'subscription_status' => 'active',
        ]);

        // Criar configurações do tenant
        TenantSettings::create([
            'tenant_id' => $tenant->id,
            'whatsapp_enabled' => $validated['lembretes_whatsapp'],
            'whatsapp_reminder_days_before' => $validated['lembretes_dias_antes'] ?? [3, 1],
            'whatsapp_reminder_on_due_date' => $validated['lembrete_dia_vencimento'],
            'whatsapp_reminder_days_after' => $validated['lembretes_dias_depois'] ?? [1, 3, 7],
            'default_currency' => $validated['moeda'],
            'default_due_days' => $validated['dias_vencimento'],
        ]);

        // Criar usuário
        $user = User::create([
            'name' => $step2['nome'],
            'email' => $step2['email'],
            'password' => Hash::make($step2['password']),
            'tenant_id' => $tenant->id,
        ]);

        // Fazer login do usuário
        Auth::login($user);

        // Limpar sessão de onboarding
        session()->forget(['onboarding_step1', 'onboarding_step2', 'onboarding_step3']);

        // Redirecionar para o painel
        return redirect()->route('painel.index')->with('success', 'Bem-vindo! Seu cadastro foi concluído com sucesso.');
    }
}
