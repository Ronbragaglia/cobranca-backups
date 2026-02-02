<?php

use App\Http\Controllers\PainelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\WhatsAppReminderSettingsController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\SaasController;
use App\Http\Controllers\BetaTesterController;
use App\Http\Controllers\LaunchDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Onboarding
Route::get('/onboarding', [OnboardingController::class, 'step1'])->name('onboarding.step1');
Route::post('/onboarding/step1', [OnboardingController::class, 'storeStep1'])->name('onboarding.step1.store');
Route::get('/onboarding/step2', [OnboardingController::class, 'step2'])->name('onboarding.step2');
Route::post('/onboarding/step2', [OnboardingController::class, 'storeStep2'])->name('onboarding.step2.store');
Route::get('/onboarding/step3', [OnboardingController::class, 'step3'])->name('onboarding.step3');
Route::post('/onboarding/step3', [OnboardingController::class, 'storeStep3'])->name('onboarding.step3.store');

// Painel
Route::get('/painel', [PainelController::class, 'index'])->middleware('auth')->name('painel.index');
Route::get('/painel/export', [PainelController::class, 'export'])->middleware('auth');
Route::get('/painel/historico/{cobranca}', [PainelController::class, 'historico'])->middleware('auth')->name('painel.historico');
Route::put('/painel/cobrancas/{cobranca}', [PainelController::class, 'updateCobranca'])->middleware('auth')->name('painel.cobrancas.update');
Route::delete('/painel/cobrancas/{cobranca}', [PainelController::class, 'destroyCobranca'])->middleware('auth')->name('painel.cobrancas.destroy');

// WhatsApp Settings
Route::get('/whatsapp-settings', [WhatsAppReminderSettingsController::class, 'edit'])->middleware('auth')->name('whatsapp-settings.edit');
Route::put('/whatsapp-settings', [WhatsAppReminderSettingsController::class, 'update'])->middleware('auth')->name('whatsapp-settings.update');

// Relatórios
Route::get('/relatorios', [RelatorioController::class, 'index'])->middleware('auth')->name('relatorios.index');
Route::get('/relatorios/export', [RelatorioController::class, 'export'])->middleware('auth')->name('relatorios.export');

// Stripe
Route::get('/cobrancas/{id}/stripe-pay', [StripeController::class, 'createPaymentLink'])->middleware('auth')->name('stripe.pay');
Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');

// Tenants (Admin)
Route::get('/tenants', [\App\Http\Controllers\TenantController::class, 'index'])->middleware('auth')->name('tenants.index');
Route::get('/tenants/create', [\App\Http\Controllers\TenantController::class, 'create'])->middleware('auth')->name('tenants.create');
Route::post('/tenants', [\App\Http\Controllers\TenantController::class, 'store'])->middleware('auth')->name('tenants.store');

// Admin
Route::get('/admin/metrics', [\App\Http\Controllers\MetricsController::class, 'index'])->middleware('auth')->name('admin.metrics');

// Beta Testers (Admin)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/beta-testers', [BetaTesterController::class, 'index'])->name('admin.beta-testers.index');
    Route::get('/admin/beta-testers/create', [BetaTesterController::class, 'create'])->name('admin.beta-testers.create');
    Route::post('/admin/beta-testers', [BetaTesterController::class, 'store'])->name('admin.beta-testers.store');
    Route::get('/admin/beta-testers/{betaTester}', [BetaTesterController::class, 'show'])->name('admin.beta-testers.show');
    Route::get('/admin/beta-testers/{betaTester}/edit', [BetaTesterController::class, 'edit'])->name('admin.beta-testers.edit');
    Route::put('/admin/beta-testers/{betaTester}', [BetaTesterController::class, 'update'])->name('admin.beta-testers.update');
    Route::delete('/admin/beta-testers/{betaTester}', [BetaTesterController::class, 'destroy'])->name('admin.beta-testers.destroy');
    Route::post('/admin/beta-testers/{betaTester}/invite', [BetaTesterController::class, 'invite'])->name('admin.beta-testers.invite');
    Route::post('/admin/beta-testers/bulk-invite', [BetaTesterController::class, 'bulkInvite'])->name('admin.beta-testers.bulk-invite');
    Route::get('/admin/beta-testers/statistics', [BetaTesterController::class, 'statistics'])->name('admin.beta-testers.statistics');
});

// Beta Tester Public Routes
Route::get('/beta/accept-invitation/{token}', [BetaTesterController::class, 'acceptInvitation'])->name('beta.accept-invitation');
Route::post('/beta/complete-onboarding', [BetaTesterController::class, 'completeOnboarding'])->name('beta.complete-onboarding');

// Client Dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/client/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');
    Route::get('/client/settings', [ClientDashboardController::class, 'settings'])->name('client.settings');
    Route::put('/client/settings', [ClientDashboardController::class, 'updateSettings'])->name('client.settings.update');
    Route::get('/client/api-keys', [ClientDashboardController::class, 'apiKeys'])->name('client.api-keys');
    Route::post('/client/api-keys', [ClientDashboardController::class, 'createApiKey'])->name('client.api-keys.create');
    Route::delete('/client/api-keys/{apiKey}', [ClientDashboardController::class, 'deleteApiKey'])->name('client.api-keys.delete');
    Route::get('/client/templates', [ClientDashboardController::class, 'templates'])->name('client.templates');
    Route::post('/client/templates', [ClientDashboardController::class, 'createTemplate'])->name('client.templates.create');
    Route::put('/client/templates/{template}', [ClientDashboardController::class, 'updateTemplate'])->name('client.templates.update');
    Route::delete('/client/templates/{template}', [ClientDashboardController::class, 'deleteTemplate'])->name('client.templates.delete');
    Route::post('/client/upload-csv', [ClientDashboardController::class, 'uploadCsv'])->name('client.upload-csv');
    Route::get('/client/qr-code', [ClientDashboardController::class, 'qrCode'])->name('client.qr-code');
    Route::post('/client/qr-code', [ClientDashboardController::class, 'uploadQrCode'])->name('client.qr-code.upload');
    Route::get('/client/analytics', [ClientDashboardController::class, 'analytics'])->name('client.analytics');
});

// Subscriptions
Route::middleware(['auth'])->group(function () {
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
    Route::get('/subscriptions/success', [SubscriptionController::class, 'success'])->name('subscriptions.success');
    Route::get('/subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancelSubscription'])->name('subscriptions.cancel-subscription');
    Route::post('/subscriptions/upgrade/{plan}', [SubscriptionController::class, 'upgrade'])->name('subscriptions.upgrade');
    Route::post('/subscriptions/webhook', [SubscriptionController::class, 'webhook'])->name('subscriptions.webhook');
});

// Admin SaaS
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/saas/dashboard', [SaasController::class, 'dashboard'])->name('admin.saas.dashboard');
    Route::get('/admin/saas/tenants', [SaasController::class, 'tenants'])->name('admin.saas.tenants');
    Route::get('/admin/saas/tenants/{tenant}', [SaasController::class, 'showTenant'])->name('admin.saas.tenants.show');
    Route::put('/admin/saas/tenants/{tenant}', [SaasController::class, 'updateTenant'])->name('admin.saas.tenants.update');
    Route::post('/admin/saas/tenants/{tenant}/deactivate', [SaasController::class, 'deactivateTenant'])->name('admin.saas.tenants.deactivate');
    Route::post('/admin/saas/tenants/{tenant}/activate', [SaasController::class, 'activateTenant'])->name('admin.saas.tenants.activate');
    Route::post('/admin/saas/tenants/create', [SaasController::class, 'createTenant'])->name('admin.saas.tenants.create');
    Route::get('/admin/saas/subscriptions', [SaasController::class, 'subscriptions'])->name('admin.saas.subscriptions');
    Route::get('/admin/saas/subscriptions/{subscription}', [SaasController::class, 'showSubscription'])->name('admin.saas.subscriptions.show');
    Route::get('/admin/saas/plans', [SaasController::class, 'plans'])->name('admin.saas.plans');
    Route::post('/admin/saas/plans', [SaasController::class, 'createPlan'])->name('admin.saas.plans.create');
    Route::put('/admin/saas/plans/{plan}', [SaasController::class, 'updatePlan'])->name('admin.saas.plans.update');
    Route::delete('/admin/saas/plans/{plan}', [SaasController::class, 'deletePlan'])->name('admin.saas.plans.delete');
    Route::get('/admin/saas/api-keys', [SaasController::class, 'apiKeys'])->name('admin.saas.api-keys');
    Route::post('/admin/saas/api-keys/{apiKey}/revoke', [SaasController::class, 'revokeApiKey'])->name('admin.saas.api-keys.revoke');
    Route::get('/admin/saas/analytics', [SaasController::class, 'analytics'])->name('admin.saas.analytics');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Launch Dashboard (Admin)
Route::middleware(['auth'])->group(function () {
    Route::get('/launch-dashboard', [LaunchDashboardController::class, 'index'])->name('launch-dashboard.index');
    Route::get('/launch-dashboard/daily-sales', [LaunchDashboardController::class, 'dailySales'])->name('launch-dashboard.daily-sales');
    Route::get('/launch-dashboard/hourly-traffic', [LaunchDashboardController::class, 'hourlyTraffic'])->name('launch-dashboard.hourly-traffic');
    Route::get('/launch-dashboard/conversion-funnel', [LaunchDashboardController::class, 'conversionFunnel'])->name('launch-dashboard.conversion-funnel');
    Route::get('/launch-dashboard/beta-tester-stats', [LaunchDashboardController::class, 'betaTesterStats'])->name('launch-dashboard.beta-tester-stats');
    Route::get('/launch-dashboard/export-daily-report', [LaunchDashboardController::class, 'exportDailyReport'])->name('launch-dashboard.export-daily-report');
});

require __DIR__.'/auth.php';
