<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Services\EvolutionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PixWebhookController extends Controller
{
    protected EvolutionApiService $evolution;

    public function __construct(EvolutionApiService $evolution)
    {
        $this->evolution = $evolution;
    }

    /**
     * Webhook para Pagar.me
     */
    public function pagarMe(Request $request)
    {
        Log::info('Webhook Pagar.me recebido', ['payload' => $request->all()]);

        // Validar payload
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'status' => 'required|string',
            'reference_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Validação do webhook Pagar.me falhou', ['errors' => $validator->errors()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $status = $request->input('status');
        $referenceId = $request->input('reference_id');

        // Buscar cobrança pelo reference_id
        $cobranca = Cobranca::where('pix_reference_id', $referenceId)->first();

        if (!$cobranca) {
            Log::warning('Cobrança não encontrada para reference_id: ' . $referenceId);
            return response()->json(['error' => 'Cobrança não encontrada'], 404);
        }

        // Processar status
        switch ($status) {
            case 'paid':
            case 'confirmed':
                $this->processarPagamentoConfirmado($cobranca, 'Pagar.me');
                break;
            case 'expired':
                $this->processarPagamentoExpirado($cobranca);
                break;
            case 'canceled':
                $this->processarPagamentoCancelado($cobranca);
                break;
            default:
                Log::info('Status não processado: ' . $status, ['cobranca_id' => $cobranca->id]);
                break;
        }

        return response()->json(['success' => true]);
    }

    /**
     * Webhook para Mercado Pago
     */
    public function mercadoPago(Request $request)
    {
        Log::info('Webhook Mercado Pago recebido', ['payload' => $request->all()]);

        // Validar payload
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'status' => 'required|string',
            'external_reference' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Validação do webhook Mercado Pago falhou', ['errors' => $validator->errors()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $status = $request->input('status');
        $externalReference = $request->input('external_reference');

        // Buscar cobrança pelo external_reference
        $cobranca = Cobranca::where('pix_reference_id', $externalReference)->first();

        if (!$cobranca) {
            Log::warning('Cobrança não encontrada para external_reference: ' . $externalReference);
            return response()->json(['error' => 'Cobrança não encontrada'], 404);
        }

        // Processar status
        switch ($status) {
            case 'approved':
            case 'paid':
                $this->processarPagamentoConfirmado($cobranca, 'Mercado Pago');
                break;
            case 'expired':
                $this->processarPagamentoExpirado($cobranca);
                break;
            case 'canceled':
                $this->processarPagamentoCancelado($cobranca);
                break;
            default:
                Log::info('Status não processado: ' . $status, ['cobranca_id' => $cobranca->id]);
                break;
        }

        return response()->json(['success' => true]);
    }

    /**
     * Processar pagamento confirmado
     */
    private function processarPagamentoConfirmado(Cobranca $cobranca, string $provedor)
    {
        if ($cobranca->status === 'paga') {
            Log::info('Cobrança já está paga', ['cobranca_id' => $cobranca->id]);
            return;
        }

        // Atualizar status da cobrança
        $cobranca->update([
            'status' => 'paga',
            'data_pagamento' => now(),
        ]);

        Log::info('Pagamento confirmado', [
            'cobranca_id' => $cobranca->id,
            'provedor' => $provedor,
            'valor' => $cobranca->valor,
        ]);

        // Enviar confirmação via WhatsApp
        if ($this->evolution->isConnected()) {
            try {
                $this->evolution->sendPaymentConfirmation(
                    $cobranca->telefone,
                    $cobranca->nome,
                    number_format($cobranca->valor, 2, ',', '.')
                );
                Log::info('Confirmação de pagamento enviada via WhatsApp', ['cobranca_id' => $cobranca->id]);
            } catch (\Exception $e) {
                Log::error('Erro ao enviar confirmação via WhatsApp', [
                    'cobranca_id' => $cobranca->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Processar pagamento expirado
     */
    private function processarPagamentoExpirado(Cobranca $cobranca)
    {
        if ($cobranca->status === 'expirada') {
            Log::info('Cobrança já está expirada', ['cobranca_id' => $cobranca->id]);
            return;
        }

        // Atualizar status da cobrança
        $cobranca->update([
            'status' => 'expirada',
        ]);

        Log::info('Pagamento expirado', [
            'cobranca_id' => $cobranca->id,
            'valor' => $cobranca->valor,
        ]);
    }

    /**
     * Processar pagamento cancelado
     */
    private function processarPagamentoCancelado(Cobranca $cobranca)
    {
        if ($cobranca->status === 'cancelada') {
            Log::info('Cobrança já está cancelada', ['cobranca_id' => $cobranca->id]);
            return;
        }

        // Atualizar status da cobrança
        $cobranca->update([
            'status' => 'cancelada',
        ]);

        Log::info('Pagamento cancelado', [
            'cobranca_id' => $cobranca->id,
            'valor' => $cobranca->valor,
        ]);
    }

    /**
     * Verificar assinatura do webhook (opcional)
     */
    private function verificarAssinatura(Request $request, string $secret)
    {
        $payload = $request->getContent();
        $assinaturaRecebida = $request->header('X-Hub-Signature');

        $assinaturaEsperada = hash_hmac('sha256', $payload, $secret);

        return hash_equals($assinaturaEsperada, $assinaturaRecebida);
    }
}
