<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Tenant;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $endpoint_secret = config('services.stripe.webhook_secret'); // Add this to config

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'invoice.payment_succeeded':
                // Payment succeeded
                $invoice = $event->data->object;
                $customer_id = $invoice->customer;
                $tenant = Tenant::where('stripe_customer_id', $customer_id)->first();
                if ($tenant) {
                    $tenant->update(['subscription_status' => 'active']);
                }
                break;

            case 'invoice.payment_failed':
                // Payment failed
                $invoice = $event->data->object;
                $customer_id = $invoice->customer;
                $tenant = Tenant::where('stripe_customer_id', $customer_id)->first();
                if ($tenant) {
                    $tenant->update(['subscription_status' => 'past_due']);
                }
                break;

            case 'customer.subscription.deleted':
                // Subscription canceled
                $subscription = $event->data->object;
                $customer_id = $subscription->customer;
                $tenant = Tenant::where('stripe_customer_id', $customer_id)->first();
                if ($tenant) {
                    $tenant->update(['subscription_status' => 'canceled']);
                }
                break;

            default:
                // Handle other events
                break;
        }

        return response()->json(['status' => 'success']);
    }
}