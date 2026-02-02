<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentLink;

class StripeController extends Controller
{
    public function createPaymentLink($id)
    {
        $cobranca = Cobranca::findOrFail($id);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Create or retrieve customer
        if ($cobranca->stripe_customer_id) {
            $customer = Customer::retrieve($cobranca->stripe_customer_id);
        } else {
            $customer = Customer::create([
                'email' => 'customer@example.com', // You can add email to Cobranca model if needed
                'name' => $cobranca->descricao,
            ]);
            $cobranca->update(['stripe_customer_id' => $customer->id]);
        }

        // Create payment link
        $paymentLink = PaymentLink::create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'brl',
                        'product_data' => [
                            'name' => $cobranca->descricao,
                        ],
                        'unit_amount' => (int)($cobranca->valor * 100), // Stripe expects cents
                    ],
                    'quantity' => 1,
                ],
            ],
            'customer' => $customer->id,
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => [
                    'url' => route('painel.index'), // Redirect back to painel after payment
                ],
            ],
        ]);

        // Save payment link URL
        $cobranca->update(['stripe_payment_link' => $paymentLink->url]);

        return redirect($paymentLink->url);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET'); // Add this to .env

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type == 'checkout.session.completed') {
            $session = $event->data->object;
            // Find the cobranca by customer id
            $customerId = $session->customer;
            $cobranca = Cobranca::where('stripe_customer_id', $customerId)->first();
            if ($cobranca) {
                $cobranca->update(['status' => 'paid']);
            }
        }

        return response('OK', 200);
    }
}