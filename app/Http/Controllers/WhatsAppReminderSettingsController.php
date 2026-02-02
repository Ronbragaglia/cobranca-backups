<?php

namespace App\Http\Controllers;

use App\Models\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppReminderSettingsController extends Controller
{
    /**
     * Mostrar formulário de configuração de lembretes de WhatsApp
     */
    public function edit()
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('onboarding.step1')->with('error', 'Por favor, complete o onboarding primeiro.');
        }

        $settings = $tenant->settings ?? TenantSettings::getDefaultSettings();

        return view('whatsapp-settings.edit', compact('settings'));
    }

    /**
     * Atualizar configurações de lembretes de WhatsApp
     */
    public function update(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('onboarding.step1')->with('error', 'Por favor, complete o onboarding primeiro.');
        }

        $validated = $request->validate([
            'lembretes_whatsapp' => 'required|boolean',
            'lembretes_dias_antes' => 'nullable|array',
            'lembretes_dias_antes.*' => 'integer|min:1|max:30',
            'lembrete_dia_vencimento' => 'required|boolean',
            'lembretes_dias_depois' => 'nullable|array',
            'lembretes_dias_depois.*' => 'integer|min:1|max:30',
        ]);

        // Ordenar os arrays de dias
        if (isset($validated['lembretes_dias_antes'])) {
            sort($validated['lembretes_dias_antes']);
        }
        if (isset($validated['lembretes_dias_depois'])) {
            sort($validated['lembretes_dias_depois']);
        }

        // Atualizar ou criar configurações
        if ($tenant->settings) {
            $tenant->settings->update([
                'whatsapp_enabled' => $validated['lembretes_whatsapp'],
                'whatsapp_reminder_days_before' => $validated['lembretes_dias_antes'] ?? [],
                'whatsapp_reminder_on_due_date' => $validated['lembrete_dia_vencimento'],
                'whatsapp_reminder_days_after' => $validated['lembretes_dias_depois'] ?? [],
            ]);
        } else {
            TenantSettings::create([
                'tenant_id' => $tenant->id,
                'whatsapp_enabled' => $validated['lembretes_whatsapp'],
                'whatsapp_reminder_days_before' => $validated['lembretes_dias_antes'] ?? [],
                'whatsapp_reminder_on_due_date' => $validated['lembrete_dia_vencimento'],
                'whatsapp_reminder_days_after' => $validated['lembretes_dias_depois'] ?? [],
                'default_currency' => 'BRL',
                'default_due_days' => 7,
            ]);
        }

        return redirect()->route('painel.index')->with('success', 'Configurações de lembretes atualizadas com sucesso!');
    }
}
