<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RelatorioController extends Controller
{
    /**
     * Mostrar página de relatórios
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('onboarding.step1')->with('error', 'Por favor, complete o onboarding primeiro.');
        }

        // Construir query base
        $query = Cobranca::where('tenant_id', $tenant->id);

        // Filtros
        $status = $request->input('status');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');

        if ($status) {
            $query->where('status', $status);
        }

        if ($dataInicio) {
            $query->whereDate('data_vencimento', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->whereDate('data_vencimento', '<=', $dataFim);
        }

        $cobrancas = $query->orderBy('data_vencimento', 'desc')->get();

        // Calcular totais
        $totais = [
            'total' => $cobrancas->count(),
            'ativas' => $cobrancas->where('status', 'pendente')->where('data_vencimento', '>=', now())->count(),
            'pagas' => $cobrancas->where('status', 'pago')->count(),
            'vencidas' => $cobrancas->where('status', 'pendente')->where('data_vencimento', '<', now())->count(),
            'canceladas' => $cobrancas->where('status', 'cancelado')->count(),
            'valor_total' => $cobrancas->sum('valor'),
            'valor_pago' => $cobrancas->where('status', 'pago')->sum('valor'),
            'valor_pendente' => $cobrancas->where('status', 'pendente')->sum('valor'),
        ];

        return view('relatorios.index', compact('cobrancas', 'totais', 'status', 'dataInicio', 'dataFim'));
    }

    /**
     * Exportar relatório para CSV
     */
    public function export(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('onboarding.step1')->with('error', 'Por favor, complete o onboarding primeiro.');
        }

        // Construir query base
        $query = Cobranca::where('tenant_id', $tenant->id);

        // Filtros
        $status = $request->input('status');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');

        if ($status) {
            $query->where('status', $status);
        }

        if ($dataInicio) {
            $query->whereDate('data_vencimento', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->whereDate('data_vencimento', '<=', $dataFim);
        }

        $cobrancas = $query->orderBy('data_vencimento', 'desc')->get();

        // Criar CSV
        $csv = "ID,Descrição,Valor,Status,Vencimento,Telefone,Criado em\n";
        
        foreach ($cobrancas as $cobranca) {
            $vencimento = $cobranca->data_vencimento ? $cobranca->data_vencimento->format('d/m/Y') : '-';
            $criadoEm = $cobranca->created_at->format('d/m/Y H:i');
            
            $csv .= "\"{$cobranca->id}\",";
            $csv .= "\"{$cobranca->descricao}\",";
            $csv .= "\"{$cobranca->valor}\",";
            $csv .= "\"{$cobranca->status}\",";
            $csv .= "\"{$vencimento}\",";
            $csv .= "\"{$cobranca->telefone}\",";
            $csv .= "\"{$criadoEm}\"\n";
        }

        // Nome do arquivo com data
        $fileName = 'cobrancas_' . now()->format('Y-m-d_His') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
