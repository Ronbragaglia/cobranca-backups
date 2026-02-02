<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    private string $baseUrl;
    private string $apiKey;
    private string $instance;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->baseUrl = env('EVOLUTION_API_URL', 'http://localhost:8080');
        $this->apiKey = env('EVOLUTION_API_KEY', '');
        $this->instance = env('EVOLUTION_INSTANCE', 'cobranca-auto');
        $this->username = env('EVOLUTION_API_USER', 'admin');
        $this->password = env('EVOLUTION_API_PASSWORD', 'admin123');
    }

    /**
     * Enviar mensagem de texto
     */
    public function sendTextMessage(string $number, string $text): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->post("{$this->baseUrl}/message/sendText/{$this->instance}", [
                    'number' => $this->formatNumber($number),
                    'text' => $text,
                ]);

            if ($response->successful()) {
                Log::info('Mensagem enviada com sucesso via Evolution API', [
                    'number' => $number,
                    'text' => $text,
                    'response' => $response->json(),
                ]);

                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Erro ao enviar mensagem via Evolution API', [
                'number' => $number,
                'text' => $text,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar mensagem via Evolution API', [
                'number' => $number,
                'text' => $text,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verificar status da instância
     */
    public function getInstanceStatus(): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(10)
                ->get("{$this->baseUrl}/instance/fetchInstances");

            if ($response->successful()) {
                $instances = $response->json();
                $instanceData = collect($instances)->firstWhere('instance', $this->instance);

                return [
                    'success' => true,
                    'status' => $instanceData['state'] ?? 'unknown',
                    'data' => $instanceData,
                ];
            }

            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'unknown',
            ];
        }
    }

    /**
     * Verificar estado da conexão
     */
    public function getConnectionState(): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(10)
                ->get("{$this->baseUrl}/instance/connectionState/{$this->instance}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'state' => $data['state'] ?? 'unknown',
                    'data' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'state' => 'unknown',
            ];
        }
    }

    /**
     * Formatar número de telefone
     */
    private function formatNumber(string $number): string
    {
        // Remove caracteres não numéricos
        $number = preg_replace('/[^0-9]/', '', $number);

        // Adiciona código do país se não tiver
        if (strlen($number) === 11) {
            $number = '55' . $number;
        }

        return $number;
    }

    /**
     * Verificar se a instância está conectada
     */
    public function isConnected(): bool
    {
        $status = $this->getInstanceStatus();
        return $status['success'] && $status['status'] === 'open';
    }

    /**
     * Enviar mensagem de cobrança
     */
    public function sendCobrancaMessage(string $number, string $nome, string $valor, string $dataVencimento, string $linkPagamento = null): array
    {
        $message = "💰 *COBRANCA*\n\n";
        $message .= "Olá, *{$nome}*!\n\n";
        $message .= "Valor: *R$ {$valor}*\n";
        $message .= "Vencimento: *{$dataVencimento}*\n\n";

        if ($linkPagamento) {
            $message .= "🔗 Link para pagamento:\n{$linkPagamento}\n\n";
        }

        $message .= "Por favor, efetue o pagamento até a data de vencimento.";

        return $this->sendTextMessage($number, $message);
    }

    /**
     * Enviar lembrete de cobrança
     */
    public function sendReminderMessage(string $number, string $nome, string $valor, string $dataVencimento): array
    {
        $message = "⏰ *LEMBRETE DE COBRANCA*\n\n";
        $message .= "Olá, *{$nome}*!\n\n";
        $message .= "Você tem uma cobrança pendente:\n\n";
        $message .= "Valor: *R$ {$valor}*\n";
        $message .= "Vencimento: *{$dataVencimento}*\n\n";
        $message .= "Por favor, efetue o pagamento o mais breve possível.";

        return $this->sendTextMessage($number, $message);
    }

    /**
     * Enviar confirmação de pagamento
     */
    public function sendPaymentConfirmation(string $number, string $nome, string $valor): array
    {
        $message = "✅ *PAGAMENTO CONFIRMADO*\n\n";
        $message .= "Olá, *{$nome}*!\n\n";
        $message .= "Recebemos seu pagamento de *R$ {$valor}*\n\n";
        $message .= "Obrigado pela preferência!";

        return $this->sendTextMessage($number, $message);
    }
}
