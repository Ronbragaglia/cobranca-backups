<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\CobrancaEnvio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Mostrar dashboard admin com métricas
     */
    public function index()
    {
        // Métricas gerais
        $totalCobrancas = Cobranca::count();
        $cobrancasPagas = Cobranca::where('status', 'paga')->count();
        $cobrancasPendentes = Cobranca::where('status', 'pendente')->count();
        $cobrancasAtrasadas = Cobranca::where('data_vencimento', '<', now())
            ->where('status', '!=', 'paga')
            ->where('status', '!=', 'cancelada')
            ->count();

        $valorTotal = Cobranca::sum('valor');
        $valorPago = Cobranca::where('status', 'paga')->sum('valor');
        $valorPendente = Cobranca::where('status', 'pendente')->sum('valor');
        $valorAtrasado = Cobranca::where('data_vencimento', '<', now())
            ->where('status', '!=', 'paga')
            ->where('status', '!=', 'cancelada')
            ->sum('valor');

        // Métricas de WhatsApp
        $mensagensEnviadas = CobrancaEnvio::where('created_at', '>=', now()->subDays(7))->count();
        $mensagensSucesso = CobrancaEnvio::where('created_at', '>=', now()->subDays(7))
            ->where('status', 'enviada')
            ->count();
        $mensagensErro = CobrancaEnvio::where('created_at', '>=', now()->subDays(7))
            ->where('status', 'erro')
            ->count();

        $taxaEntrega = $mensagensEnviadas > 0
            ? round(($mensagensSucesso / $mensagensEnviadas) * 100, 2)
            : 0;

        // Métricas dos últimos 7 dias
        $cobrancas7Dias = Cobranca::where('created_at', '>=', now()->subDays(7))->get();
        $cobrancasPagas7Dias = Cobranca::where('status', 'paga')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $valorPago7Dias = Cobranca::where('status', 'paga')
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('valor');

        // Gráfico de cobranças por dia (últimos 7 dias)
        $cobrancasPorDia = [];
        for ($i = 6; $i >= 0; $i--) {
            $data = now()->subDays($i)->format('Y-m-d');
            $cobrancasPorDia[] = [
                'data' => $data,
                'total' => Cobranca::whereDate('created_at', $data)->count(),
                'pagas' => Cobranca::whereDate('created_at', $data)
                    ->where('status', 'paga')
                    ->count(),
                'valor' => Cobranca::whereDate('created_at', $data)
                    ->where('status', 'paga')
                    ->sum('valor'),
            ];
        }

        // Gráfico de status
        $cobrancasPorStatus = [
            'paga' => $cobrancasPagas,
            'pendente' => $cobrancasPendentes,
            'atrasada' => $cobrancasAtrasadas,
            'cancelada' => Cobranca::where('status', 'cancelada')->count(),
        ];

        // Últimas cobranças
        $ultimasCobrancas = Cobranca::orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Cobranças vencendo hoje
        $vencendoHoje = Cobranca::whereDate('data_vencimento', today())
            ->where('status', '!=', 'paga')
            ->where('status', '!=', 'cancelada')
            ->get();

        // Status da Evolution API
        $evolutionStatus = $this->getEvolutionStatus();

        return view('admin.dashboard', compact(
            'totalCobrancas',
            'cobrancasPagas',
            'cobrancasPendentes',
            'cobrancasAtrasadas',
            'valorTotal',
            'valorPago',
            'valorPendente',
            'valorAtrasado',
            'mensagensEnviadas',
            'mensagensSucesso',
            'mensagensErro',
            'taxaEntrega',
            'cobrancas7Dias',
            'cobrancasPagas7Dias',
            'valorPago7Dias',
            'cobrancasPorDia',
            'cobrancasPorStatus',
            'ultimasCobrancas',
            'vencendoHoje',
            'evolutionStatus',
        ));
    }

    /**
     * Obter status da Evolution API
     */
    private function getEvolutionStatus()
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, env('EVOLUTION_API_URL') . '/instance/fetchInstances');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, env('EVOLUTION_API_USER') . ':' . env('EVOLUTION_API_PASSWORD'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                $instance = collect($data)->firstWhere('instance', env('EVOLUTION_INSTANCE'));

                return [
                    'status' => $instance['state'] ?? 'unknown',
                    'connected' => ($instance['state'] ?? '') === 'open',
                    'last_update' => $instance['lastUpdate'] ?? null,
                ];
            }

            return [
                'status' => 'error',
                'connected' => false,
                'error' => "HTTP $httpCode",
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obter métricas em tempo real (AJAX)
     */
    public function metrics()
    {
        // Métricas de WhatsApp
        $mensagensEnviadasHoje = CobrancaEnvio::whereDate('created_at', today())->count();
        $mensagensEnviadas7Dias = CobrancaEnvio::where('created_at', '>=', now()->subDays(7))->count();
        $mensagensSucesso7Dias = CobrancaEnvio::where('created_at', '>=', now()->subDays(7))
            ->where('status', 'enviada')
            ->count();
        $mensagensErro7Dias = CobrancaEnvio::where('created_at', '>=', now()->subDays(7))
            ->where('status', 'erro')
            ->count();

        $taxaEntrega7Dias = $mensagensEnviadas7Dias > 0
            ? round(($mensagensSucesso7Dias / $mensagensEnviadas7Dias) * 100, 2)
            : 0;

        // Status da Evolution API
        $evolutionStatus = $this->getEvolutionStatus();

        return response()->json([
            'mensagens_enviadas_hoje' => $mensagensEnviadasHoje,
            'mensagens_enviadas_7_dias' => $mensagensEnviadas7Dias,
            'mensagens_sucesso_7_dias' => $mensagensSucesso7Dias,
            'mensagens_erro_7_dias' => $mensagensErro7Dias,
            'taxa_entrega' => $taxaEntrega7Dias,
            'evolution_status' => $evolutionStatus,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Obter logs de erro
     */
    public function errorLogs()
    {
        $logs = CobrancaEnvio::where('status', 'erro')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->json($logs);
    }

    /**
     * Obter estatísticas detalhadas
     */
    public function stats()
    {
        // Estatísticas por mês
        $statsPorMes = DB::table('cobrancas')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "paga" THEN valor ELSE 0 END) as valor_pago'),
                DB::raw('SUM(CASE WHEN status = "paga" THEN 1 ELSE 0 END) as pagas'),
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('mes')
            ->orderBy('mes', 'desc')
            ->get();

        // Estatísticas por tenant
        $statsPorTenant = DB::table('cobrancas')
            ->select(
                'tenant_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(valor) as valor_total'),
                DB::raw('SUM(CASE WHEN status = "paga" THEN valor ELSE 0 END) as valor_pago'),
            )
            ->groupBy('tenant_id')
            ->get();

        return response()->json([
            'por_mes' => $statsPorMes,
            'por_tenant' => $statsPorTenant,
        ]);
    }
}
