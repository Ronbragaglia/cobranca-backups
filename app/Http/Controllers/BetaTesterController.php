<?php

namespace App\Http\Controllers;

use App\Models\BetaTester;
use App\Models\User;
use App\Models\Tenant;
use App\Models\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class BetaTesterController extends Controller
{
    /**
     * Display a listing of beta testers (Admin only)
     */
    public function index()
    {
        $betaTesters = BetaTester::orderBy('created_at', 'desc')->get();
        
        return view('admin.beta-testers.index', compact('betaTesters'));
    }

    /**
     * Show the form for creating a new beta tester
     */
    public function create()
    {
        return view('admin.beta-testers.create');
    }

    /**
     * Store a newly created beta tester
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:beta_testers,email',
            'phone' => 'required|string|max:20',
            'company' => 'required|string|max:255',
            'segment' => 'nullable|string|max:100',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $betaTester = BetaTester::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'company' => $validated['company'],
            'segment' => $validated['segment'] ?? null,
            'discount_percentage' => $validated['discount_percentage'] ?? 50,
            'notes' => $validated['notes'] ?? null,
            'status' => BetaTester::STATUS_PENDING,
        ]);

        return redirect()
            ->route('admin.beta-testers.index')
            ->with('success', 'Beta tester adicionado com sucesso!');
    }

    /**
     * Display the specified beta tester
     */
    public function show(BetaTester $betaTester)
    {
        return view('admin.beta-testers.show', compact('betaTester'));
    }

    /**
     * Show the form for editing the specified beta tester
     */
    public function edit(BetaTester $betaTester)
    {
        return view('admin.beta-testers.edit', compact('betaTester'));
    }

    /**
     * Update the specified beta tester
     */
    public function update(Request $request, BetaTester $betaTester)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:beta_testers,email,' . $betaTester->id,
            'phone' => 'required|string|max:20',
            'company' => 'required|string|max:255',
            'segment' => 'nullable|string|max:100',
            'status' => 'required|in:pending,invited,accepted,active,inactive',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'feedback_score' => 'nullable|integer|min:1|max:5',
            'referrals_count' => 'nullable|integer|min:0',
        ]);

        $betaTester->update($validated);

        return redirect()
            ->route('admin.beta-testers.index')
            ->with('success', 'Beta tester atualizado com sucesso!');
    }

    /**
     * Remove the specified beta tester
     */
    public function destroy(BetaTester $betaTester)
    {
        $betaTester->delete();

        return redirect()
            ->route('admin.beta-testers.index')
            ->with('success', 'Beta tester removido com sucesso!');
    }

    /**
     * Invite beta tester
     */
    public function invite(BetaTester $betaTester)
    {
        if ($betaTester->status !== BetaTester::STATUS_PENDING) {
            return back()->with('error', 'Este beta tester já foi convidado.');
        }

        $betaTester->update([
            'status' => BetaTester::STATUS_INVITED,
            'invited_at' => now(),
        ]);

        // Enviar email de convite
        $this->sendInvitationEmail($betaTester);

        return back()->with('success', 'Convite enviado com sucesso!');
    }

    /**
     * Accept beta tester invitation
     */
    public function acceptInvitation(Request $request, $token)
    {
        $betaTester = BetaTester::where('email', $request->email)->first();

        if (!$betaTester || $betaTester->status !== BetaTester::STATUS_INVITED) {
            return redirect()->route('login')->with('error', 'Convite inválido ou expirado.');
        }

        return view('beta.accept-invitation', compact('betaTester'));
    }

    /**
     * Complete beta tester onboarding
     */
    public function completeOnboarding(Request $request)
    {
        $validated = $request->validate([
            'beta_tester_id' => 'required|exists:beta_testers,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'company_name' => 'required|string|max:255',
            'company_subdomain' => 'required|string|max:50|regex:/^[a-z0-9-]+$/|unique:tenants,subdomain',
        ]);

        $betaTester = BetaTester::find($validated['beta_tester_id']);

        if (!$betaTester || $betaTester->email !== $validated['email']) {
            return back()->with('error', 'Dados inválidos.');
        }

        // Criar tenant
        $tenant = Tenant::create([
            'name' => $validated['company_name'],
            'subdomain' => $validated['company_subdomain'],
            'subscription_status' => 'active',
            'plan_id' => 1, // Plano básico por padrão
            'beta_tester' => true,
            'discount_percentage' => $betaTester->discount_percentage,
        ]);

        // Criar configurações do tenant
        TenantSettings::create([
            'tenant_id' => $tenant->id,
            'whatsapp_enabled' => true,
            'whatsapp_reminder_days_before' => [3, 1],
            'whatsapp_reminder_on_due_date' => true,
            'whatsapp_reminder_days_after' => [1, 3, 7],
            'default_currency' => 'BRL',
            'default_due_days' => 30,
        ]);

        // Criar usuário
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'tenant_id' => $tenant->id,
            'is_beta_tester' => true,
        ]);

        // Atualizar beta tester
        $betaTester->update([
            'status' => BetaTester::STATUS_ACTIVE,
            'accepted_at' => now(),
        ]);

        // Fazer login do usuário
        Auth::login($user);

        // Redirecionar para o painel
        return redirect()->route('painel.index')->with('success', 'Bem-vindo ao programa Beta! Você tem 50% de desconto vitalício.');
    }

    /**
     * Send invitation email to beta tester
     */
    private function sendInvitationEmail(BetaTester $betaTester)
    {
        $token = Str::random(32);
        
        // Aqui você pode implementar o envio de email
        // usando o sistema de email do Laravel
        
        // Exemplo:
        // Mail::to($betaTester->email)
        //     ->send(new BetaTesterInvitation($betaTester, $token));
        
        // Por enquanto, apenas log
        \Log::info("Convite enviado para beta tester: {$betaTester->email}");
    }

    /**
     * Get beta tester statistics
     */
    public function statistics()
    {
        $stats = [
            'total' => BetaTester::count(),
            'pending' => BetaTester::where('status', BetaTester::STATUS_PENDING)->count(),
            'invited' => BetaTester::where('status', BetaTester::STATUS_INVITED)->count(),
            'active' => BetaTester::where('status', BetaTester::STATUS_ACTIVE)->count(),
            'average_feedback_score' => BetaTester::whereNotNull('feedback_score')->avg('feedback_score'),
            'total_referrals' => BetaTester::sum('referrals_count'),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk invite beta testers
     */
    public function bulkInvite(Request $request)
    {
        $validated = $request->validate([
            'beta_tester_ids' => 'required|array',
            'beta_tester_ids.*' => 'exists:beta_testers,id',
        ]);

        $count = 0;
        foreach ($validated['beta_tester_ids'] as $id) {
            $betaTester = BetaTester::find($id);
            if ($betaTester && $betaTester->status === BetaTester::STATUS_PENDING) {
                $betaTester->update([
                    'status' => BetaTester::STATUS_INVITED,
                    'invited_at' => now(),
                ]);
                $this->sendInvitationEmail($betaTester);
                $count++;
            }
        }

        return back()->with('success', "{$count} convites enviados com sucesso!");
    }
}
