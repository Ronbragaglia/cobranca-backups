<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Price;
use Stripe\Subscription as StripeSubscription;

class SubscriptionController extends Controller
{
    public function index()
    {
        $tenant = Auth::user()->tenant;
        $subscription = $tenant->subscription;
        $plans = Plan::active()->ordered()->get();

        return view('subscriptions.index', compact('tenant', 'subscription', 'plans'));
    }

    public function checkout(Request $request, Plan $plan)
    {
        $tenant = Auth::user()->tenant;

        // Verificar se já tem uma assinatura ativa
        if ($tenant->subscription && $tenant->subscription->isActive()) {
            return back()->with('error', 'Você já possui uma assinatura ativa.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        // Criar ou recuperar cliente no Stripe
        $customer = $this->getOrCreateStripeCustomer($tenant);

        // Criar preço no Stripe se não existir
        if (!$plan->stripe_price_id) {
            $price = Price::create([
                'unit_amount' => $plan->price * 100,
                'currency' => $plan->currency,
                'recurring' => [
                    'interval' => 'month',
                    'interval_count' => $plan->interval,
                ],
                'product_data' => [
                    'name' => $plan->name,
                    'description' => $plan->description,
                ],
            ]);

            $plan->update(['stripe_price_id' => $price->id]);
        }

        // Criar checkout session
        $checkoutSession = \Stripe\Checkout\Session::create([
            'customer' => $customer->id,
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price' => $plan->stripe_price_id,
                    'quantity' => 1,
                ],
            ],
            'mode' => 'subscription',
            'success_url' => route('subscriptions.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('subscriptions.cancel'),
            'metadata' => [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
            ],
        ]);

        return redirect($checkoutSession->url);
    }

    public function success(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $sessionId = $request->get('session_id');
        $session = \Stripe\Checkout\Session::retrieve($sessionId);

        if (!$session || $session->status !== 'complete') {
            return redirect()->route('subscriptions.index')->with('error', 'Pagamento não concluído.');
        }

        $tenantId = $session->metadata->tenant_id ?? null;
        $planId = $session->metadata->plan_id ?? null;

        if (!$tenantId || !$planId) {
            return redirect()->route('subscriptions.index')->with('error', 'Erro ao processar assinatura.');
        }

        $tenant = Tenant::find($tenantId);
        $plan = Plan::find($planId);

        if (!$tenant || !$plan) {
            return redirect()->route('subscriptions.index')->with('error', 'Erro ao processar assinatura.');
        }

        // Criar assinatura no banco de dados
        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'stripe_subscription_id' => $session->subscription,
            'stripe_customer_id' => $session->customer,
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

        // Atualizar tenant
        $tenant->update([
            'stripe_customer_id' => $session->customer,
            'subscription_status' => 'active',
            'max_whatsapp_instances' => $plan->max_whatsapp_instances,
            'max_messages_per_month' => $plan->max_messages_per_month,
        ]);

        return redirect()->route('subscriptions.index')->with('success', 'Assinatura criada com sucesso!');
    }

    public function cancel()
    {
        return redirect()->route('subscriptions.index')->with('error', 'Pagamento cancelado.');
    }

    public function cancelSubscription(Request $request)
    {
        $tenant = Auth::user()->tenant;
        $subscription = $tenant->subscription;

        if (!$subscription || !$subscription->isActive()) {
            return back()->with('error', 'Nenhuma assinatura ativa encontrada.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // Cancelar no Stripe
            $stripeSubscription = StripeSubscription::retrieve($subscription->stripe_subscription_id);
            $stripeSubscription->cancel_at_period_end = true;
            $stripeSubscription->save();

            // Atualizar no banco
            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
            ]);

            $tenant->update(['subscription_status' => 'canceled']);

            return back()->with('success', 'Assinatura cancelada. Você continuará com acesso até o fim do período atual.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao cancelar assinatura: ' . $e->getMessage());
        }
    }

    public function upgrade(Request $request, Plan $plan)
    {
        $tenant = Auth::user()->tenant;
        $subscription = $tenant->subscription;

        if (!$subscription || !$subscription->isActive()) {
            return back()->with('error', 'Nenhuma assinatura ativa encontrada.');
        }

        if ($subscription->plan_id === $plan->id) {
            return back()->with('error', 'Você já está neste plano.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $stripeSubscription = StripeSubscription::retrieve($subscription->stripe_subscription_id);

            // Atualizar assinatura no Stripe
            $stripeSubscription = StripeSubscription::update($subscription->stripe_subscription_id, [
                'items' => [
                    [
                        'id' => $stripeSubscription->items->data[0]->id,
                        'price' => $plan->stripe_price_id,
                    ],
                ],
            ]);

            // Atualizar no banco
            $subscription->update([
                'plan_id' => $plan->id,
                'stripe_status' => $stripeSubscription->status,
                'status' => $stripeSubscription->status,
            ]);

            // Atualizar tenant
            $tenant->update([
                'max_whatsapp_instances' => $plan->max_whatsapp_instances,
                'max_messages_per_month' => $plan->max_messages_per_month,
            ]);

            return back()->with('success', 'Plano atualizado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao atualizar plano: ' . $e->getMessage());
        }
    }

    public function webhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $stripeSubscription = $event->data->object;
                $this->handleSubscriptionUpdated($stripeSubscription);
                break;

            case 'customer.subscription.deleted':
                $stripeSubscription = $event->data->object;
                $this->handleSubscriptionDeleted($stripeSubscription);
                break;

            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;
                $this->handleInvoicePaymentSucceeded($invoice);
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $this->handleInvoicePaymentFailed($invoice);
                break;
        }

        return response()->json(['success' => true]);
    }

    private function getOrCreateStripeCustomer(Tenant $tenant): Customer
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        if ($tenant->stripe_customer_id) {
            try {
                return Customer::retrieve($tenant->stripe_customer_id);
            } catch (\Exception $e) {
                // Cliente não existe, criar novo
            }
        }

        $customer = Customer::create([
            'name' => $tenant->name,
            'email' => $tenant->users->first()->email ?? null,
            'metadata' => [
                'tenant_id' => $tenant->id,
            ],
        ]);

        $tenant->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    private function handleSubscriptionUpdated($stripeSubscription): void
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            $subscription->update([
                'stripe_status' => $stripeSubscription->status,
                'status' => $stripeSubscription->status,
                'ends_at' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
            ]);
        }
    }

    private function handleSubscriptionDeleted($stripeSubscription): void
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
            ]);

            $subscription->tenant->update(['subscription_status' => 'canceled']);
        }
    }

    private function handleInvoicePaymentSucceeded($invoice): void
    {
        $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'active',
            ]);

            $subscription->tenant->update(['subscription_status' => 'active']);
        }
    }

    private function handleInvoicePaymentFailed($invoice): void
    {
        $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'past_due',
            ]);

            $subscription->tenant->update(['subscription_status' => 'past_due']);
        }
    }
}
