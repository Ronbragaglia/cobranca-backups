<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\CobrancaEnvio;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PainelController extends Controller
{
    public function index()
    {
        // Obter tenant do usuário autenticado
        $tenant = auth()->user()->tenant;

        // Filtrar cobranças por tenant
        $cobrancas = $tenant ? Cobranca::where('tenant_id', $tenant->id)->get() : collect();

        // Criar 3 cobranças teste se vazio e tiver tenant
        if ($cobrancas->isEmpty() && $tenant) {
            Cobranca::create([
                'descricao' => 'Cobrança Teste 1',
                'valor' => 100.00,
                'status' => 'pendente',
                'data_vencimento' => now()->addDays(7),
                'telefone' => '11999999999',
                'tenant_id' => $tenant->id,
            ]);
            Cobranca::create([
                'descricao' => 'Cobrança Teste 2',
                'valor' => 200.00,
                'status' => 'pago',
                'data_vencimento' => now()->addDays(14),
                'telefone' => '11999999998',
                'tenant_id' => $tenant->id,
            ]);
            Cobranca::create([
                'descricao' => 'Cobrança Teste 3',
                'valor' => 150.00,
                'status' => 'pendente',
                'data_vencimento' => now()->addDays(21),
                'telefone' => '11999999997',
                'tenant_id' => $tenant->id,
            ]);
            // Recarregar cobranças
            $cobrancas = Cobranca::where('tenant_id', $tenant->id)->get();
        }

        // Métricas do dashboard
        $ativas = $cobrancas->where('status', 'pendente')->count();
        $pagas = $cobrancas->where('status', 'pago')->count();
        $vencidas = $cobrancas->where('status', 'pendente')->where('data_vencimento', '<', now())->count();
        $canceladas = $cobrancas->where('status', 'cancelado')->count();

        // Valor a receber nos próximos 7 e 30 dias
        $valor7dias = $cobrancas
            ->where('status', 'pendente')
            ->where('data_vencimento', '>=', now())
            ->where('data_vencimento', '<=', now()->addDays(7))
            ->sum('valor');

        $valor30dias = $cobrancas
            ->where('status', 'pendente')
            ->where('data_vencimento', '>=', now())
            ->where('data_vencimento', '<=', now()->addDays(30))
            ->sum('valor');

        // Gráfico de cobranças por dia nos últimos 14 dias
        $chartData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = $cobrancas->where('created_at', '>=', $date->startOfDay())
                ->where('created_at', '<=', $date->endOfDay())
                ->count();
            $chartData[] = [
                'date' => $date->format('d/m'),
                'count' => $count,
            ];
        }

        return view('painel', compact(
            'cobrancas',
            'ativas',
            'pagas',
            'vencidas',
            'canceladas',
            'valor7dias',
            'valor30dias',
            'chartData'
        ));
    }

    public function export()
    {
        $query = Cobranca::query();

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($busca = request('busca')) {
            $query->where('descricao', 'like', '%' . $busca . '%');
        }

        if ($data_inicio = request('data_inicio')) {
            $query->whereDate('created_at', '>=', $data_inicio);
        }

        if ($data_fim = request('data_fim')) {
            $query->whereDate('created_at', '<=', $data_fim);
        }

        if ($valor_min = request('valor_min')) {
            $query->where('valor', '>=', $valor_min);
        }

        if ($valor_max = request('valor_max')) {
            $query->where('valor', '<=', $valor_max);
        }

        $cobrancas = $query->latest()->get();

        $csv = "ID,Descrição,Valor,Status,Criado em\n";
        foreach ($cobrancas as $c) {
            $csv .= "{$c->id},\"{$c->descricao}\",{$c->valor},{$c->status},{$c->created_at->format('d/m/Y H:i')}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="cobrancas.csv"');
    }

    public function updateCobranca(Request $request, Cobranca $cobranca)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'status' => 'required|in:pendente,pago,cancelado',
            'data_vencimento' => 'required|date|after:today',
            'telefone' => 'required|string|regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/',
        ]);

        $cobranca->update($request->only(['descricao', 'valor', 'status', 'data_vencimento', 'telefone']));

        return redirect()->route('painel.index')->with('success', 'Cobrança atualizada com sucesso!');
    }

    public function destroyCobranca(Cobranca $cobranca)
    {
        $cobranca->delete();

        return redirect()->route('painel.index')->with('success', 'Cobrança excluída com sucesso!');
    }

    public function historico(Cobranca $cobranca)
    {
        $envios = CobrancaEnvio::where('cobranca_id', $cobranca->id)->orderBy('data', 'desc')->get();

        return view('historico', compact('cobranca', 'envios'));
    }


}