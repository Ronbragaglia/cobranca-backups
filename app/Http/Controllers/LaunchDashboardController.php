<?php

namespace App\Http\Controllers;

use App\Models\BetaTester;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Cobranca;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaunchDashboardController extends Controller
{
    /**
     * Display launch dashboard
     */
    public function index()
    {
        // Métricas de hoje
        $today = Carbon::today();
        
        // Vendas do dia
        $salesToday = Subscription::whereDate('created_at', $today)->count();
        $mrrToday = Subscription::whereDate('created_at', $today)
            ->where('status', 'active')
            ->sum('amount');
        
        // Leads do dia
        $leadsToday = User::whereDate('created_at', $today)->count();
        
        // Visitas (simulado - integrar com GA4)
        $visitsToday = $this->getVisitsFromGA4($today);
        
        // Conversão
        $conversionRate = $visitsToday > 0 ? ($leadsToday / $visitsToday) * 100 : 0;
        
        // CAC (Custo por Aquisição de Cliente)
        $cac = $salesToday > 0 ? 500 / $salesToday : 0; // R$500/dia de ads
        
        // LTV (Lifetime Value)
        $ltv = $salesToday > 0 ? $mrrToday / $salesToday : 0;
        
        // ROAS (Return on Ad Spend)
        $roas = $cac > 0 ? ($ltv / $cac) * 100 : 0;
        
        // Métricas da semana
        $weekStart = Carbon::now()->startOfWeek();
        $salesWeek = Subscription::where('created_at', '>=', $weekStart)->count();
        $mrrWeek = Subscription::where('created_at', '>=', $weekStart)
            ->where('status', 'active')
            ->sum('amount');
        
        // Métricas do mês
        $monthStart = Carbon::now()->startOfMonth();
        $salesMonth = Subscription::where('created_at', '>=', $monthStart)->count();
        $mrrMonth = Subscription::where('created_at', '>=', $monthStart)
            ->where('status', 'active')
            ->sum('amount');
        
        // Beta testers
        $betaTestersTotal = BetaTester::count();
        $betaTestersActive = BetaTester::where('status', BetaTester::STATUS_ACTIVE)->count();
        $betaTestersPending = BetaTester::where('status', BetaTester::STATUS_PENDING)->count();
        
        // Cobranças
        $cobrancasToday = Cobranca::whereDate('created_at', $today)->count();
        $cobrancasSentToday = Cobranca::whereDate('created_at', $today)
            ->where('status', 'sent')
            ->count();
        $cobrancasPaidToday = Cobranca::whereDate('updated_at', $today)
            ->where('status', 'paid')
            ->count();
        
        // Retenção
        $retentionD7 = $this->calculateRetention(7);
        $retentionD30 = $this->calculateRetention(30);
        
        // Churn rate
        $churnRate = $this->calculateChurnRate();
        
        // Tráfego por fonte
        $trafficBySource = $this->getTrafficBySource($today);
        
        // Vendas por plano
        $salesByPlan = $this->getSalesByPlan($today);
        
        // Onboarding funnel
        $onboardingFunnel = $this->getOnboardingFunnel($today);
        
        return view('launch-dashboard', compact(
            'today',
            'salesToday',
            'mrrToday',
            'leadsToday',
            'visitsToday',
            'conversionRate',
            'cac',
            'ltv',
            'roas',
            'salesWeek',
            'mrrWeek',
            'salesMonth',
            'mrrMonth',
            'betaTestersTotal',
            'betaTestersActive',
            'betaTestersPending',
            'cobrancasToday',
            'cobrancasSentToday',
            'cobrancasPaidToday',
            'retentionD7',
            'retentionD30',
            'churnRate',
            'trafficBySource',
            'salesByPlan',
            'onboardingFunnel'
        ));
    }

    /**
     * Get visits from GA4 (simulated)
     */
    private function getVisitsFromGA4($date)
    {
        // Simulação - integrar com GA4 API
        // Exemplo de integração:
        // $response = Http::get('https://analyticsreporting.googleapis.com/v4/reports', [
        //     'query' => [
        //         'ids' => 'ga:XXXXXXXXXX',
        //         'start-date' => $date->format('Y-m-d'),
        //         'end-date' => $date->format('Y-m-d'),
        //         'metrics' => 'ga:sessions'
        //     ]
        // ]);
        // return $response->json()['reports'][0]['data']['totals'][0]['values'][0];
        
        // Simulação: 1000 visitas/dia
        return 1000;
    }

    /**
     * Calculate retention rate
     */
    private function calculateRetention($days)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $usersStart = User::where('created_at', '<=', $startDate)->count();
        $usersEnd = User::where('created_at', '<=', $startDate)
            ->where('last_login_at', '>=', Carbon::now()->subDays(7))
            ->count();
        
        return $usersStart > 0 ? ($usersEnd / $usersStart) * 100 : 0;
    }

    /**
     * Calculate churn rate
     */
    private function calculateChurnRate()
    {
        $monthStart = Carbon::now()->startOfMonth();
        
        $activeUsers = User::where('created_at', '<', $monthStart)->count();
        $churnedUsers = User::where('last_login_at', '<', $monthStart)
            ->where('created_at', '<', $monthStart)
            ->count();
        
        return $activeUsers > 0 ? ($churnedUsers / $activeUsers) * 100 : 0;
    }

    /**
     * Get traffic by source
     */
    private function getTrafficBySource($date)
    {
        // Simulação - integrar com GA4 API
        return [
            'organic' => 30,
            'direct' => 25,
            'social' => 20,
            'referral' => 15,
            'email' => 10,
        ];
    }

    /**
     * Get sales by plan
     */
    private function getSalesByPlan($date)
    {
        return Subscription::whereDate('created_at', $date)
            ->select('plan_id', DB::raw('count(*) as count'))
            ->groupBy('plan_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->plan_id => $item->count];
            });
    }

    /**
     * Get onboarding funnel
     */
    private function getOnboardingFunnel($date)
    {
        return [
            'signups' => User::whereDate('created_at', $date)->count(),
            'onboarding_started' => User::whereDate('created_at', $date)
                ->where('onboarding_completed', true)
                ->count(),
            'onboarding_completed' => User::whereDate('created_at', $date)
                ->where('onboarding_completed', true)
                ->where('qr_code_scanned', true)
                ->count(),
            'first_cobranca' => Cobranca::whereDate('created_at', $date)
                ->where('is_first', true)
                ->count(),
        ];
    }

    /**
     * Get daily sales data for chart
     */
    public function dailySales()
    {
        $data = Subscription::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as sales'),
                DB::raw('sum(amount) as revenue')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return response()->json($data);
    }

    /**
     * Get hourly traffic data for chart
     */
    public function hourlyTraffic()
    {
        // Simulação - integrar com GA4 API
        $data = [];
        for ($i = 0; $i < 24; $i++) {
            $data[] = [
                'hour' => $i,
                'visits' => rand(20, 100),
            ];
        }
        
        return response()->json($data);
    }

    /**
     * Get conversion funnel data
     */
    public function conversionFunnel()
    {
        $today = Carbon::today();
        
        $funnel = [
            'visits' => $this->getVisitsFromGA4($today),
            'signups' => User::whereDate('created_at', $today)->count(),
            'onboarding' => User::whereDate('created_at', $today)
                ->where('onboarding_completed', true)
                ->count(),
            'active_users' => User::whereDate('created_at', $today)
                ->where('last_login_at', '>=', Carbon::now()->subDay())
                ->count(),
            'sales' => Subscription::whereDate('created_at', $today)->count(),
        ];
        
        return response()->json($funnel);
    }

    /**
     * Get beta tester statistics
     */
    public function betaTesterStats()
    {
        $stats = [
            'total' => BetaTester::count(),
            'pending' => BetaTester::where('status', BetaTester::STATUS_PENDING)->count(),
            'invited' => BetaTester::where('status', BetaTester::STATUS_INVITED)->count(),
            'active' => BetaTester::where('status', BetaTester::STATUS_ACTIVE)->count(),
            'average_feedback_score' => BetaTester::whereNotNull('feedback_score')->avg('feedback_score'),
            'total_referrals' => BetaTester::sum('referrals_count'),
            'conversion_rate' => $this->calculateBetaTesterConversion(),
        ];
        
        return response()->json($stats);
    }

    /**
     * Calculate beta tester conversion rate
     */
    private function calculateBetaTesterConversion()
    {
        $total = BetaTester::count();
        $active = BetaTester::where('status', BetaTester::STATUS_ACTIVE)->count();
        
        return $total > 0 ? ($active / $total) * 100 : 0;
    }

    /**
     * Export daily report
     */
    public function exportDailyReport()
    {
        $today = Carbon::today();
        
        $data = [
            'date' => $today->format('Y-m-d'),
            'sales' => Subscription::whereDate('created_at', $today)->count(),
            'mrr' => Subscription::whereDate('created_at', $today)
                ->where('status', 'active')
                ->sum('amount'),
            'leads' => User::whereDate('created_at', $today)->count(),
            'visits' => $this->getVisitsFromGA4($today),
            'conversion_rate' => $this->calculateConversionRate($today),
            'cac' => $this->calculateCAC($today),
            'ltv' => $this->calculateLTV($today),
            'roas' => $this->calculateROAS($today),
            'beta_testers_active' => BetaTester::where('status', BetaTester::STATUS_ACTIVE)->count(),
            'cobrancas_sent' => Cobranca::whereDate('created_at', $today)
                ->where('status', 'sent')
                ->count(),
            'cobrancas_paid' => Cobranca::whereDate('updated_at', $today)
                ->where('status', 'paid')
                ->count(),
        ];
        
        return response()->json($data);
    }

    /**
     * Calculate conversion rate for a date
     */
    private function calculateConversionRate($date)
    {
        $visits = $this->getVisitsFromGA4($date);
        $leads = User::whereDate('created_at', $date)->count();
        
        return $visits > 0 ? ($leads / $visits) * 100 : 0;
    }

    /**
     * Calculate CAC for a date
     */
    private function calculateCAC($date)
    {
        $sales = Subscription::whereDate('created_at', $date)->count();
        return $sales > 0 ? 500 / $sales : 0;
    }

    /**
     * Calculate LTV for a date
     */
    private function calculateLTV($date)
    {
        $sales = Subscription::whereDate('created_at', $date)->count();
        $mrr = Subscription::whereDate('created_at', $date)
            ->where('status', 'active')
            ->sum('amount');
        
        return $sales > 0 ? $mrr / $sales : 0;
    }

    /**
     * Calculate ROAS for a date
     */
    private function calculateROAS($date)
    {
        $cac = $this->calculateCAC($date);
        $ltv = $this->calculateLTV($date);
        
        return $cac > 0 ? ($ltv / $cac) * 100 : 0;
    }
}
