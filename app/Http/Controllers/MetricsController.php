<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class MetricsController extends Controller
{
    public function index()
    {
        // MRR: Monthly Recurring Revenue - sum of active subscriptions
        // Assuming each tenant has a monthly subscription, say $10 for simplicity
        $activeTenants = Tenant::where('subscription_status', 'active')->count();
        $mrr = $activeTenants * 10; // Replace with actual pricing logic

        // Churn: rate of cancellations
        $totalTenants = Tenant::count();
        $canceledTenants = Tenant::where('subscription_status', 'canceled')->count();
        $churnRate = $totalTenants > 0 ? ($canceledTenants / $totalTenants) * 100 : 0;

        return view('admin.metrics', compact('mrr', 'churnRate', 'activeTenants', 'totalTenants'));
    }
}