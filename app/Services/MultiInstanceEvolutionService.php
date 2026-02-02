<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MultiInstanceEvolutionService
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private array $instances;

    public function __construct()
    {
        $this->baseUrl = env('EVOLUTION_API_URL', 'http://localhost:8080');
        $this->username = env('EVOLUTION_API_USER', 'admin');
        $this->password = env('EVOLUTION_API_PASSWORD', 'admin123');
        $this->loadInstances();
    }

    /**
     * Carregar instâncias configuradas
     */
    private function loadInstances()
    {
        $this->instances = [
            'cobranca1' => [
                'name' => 'cobranca1',
                'tenant_id' => 1,
                'priority' => 1,
            ],
            'cobranca2' => [
                'name' => 'cobranca2',
                'tenant_id' => 2,
                'priority' => 2,
            ],
            'cobranca3' => [
                'name' => 'cobranca3',
                'tenant_id' => 3,
                'priority' => 3,
            ],
            'cobranca4' => [
                'name' => 'cobranca4',
                'tenant_id' => 4,
                'priority' => 4,
            ],
            'cobranca5' => [
                'name' => 'cobranca5',
                'tenant_id' => 5,
                'priority' => 5,
            ],
        ];
    }

    /**
     * Obter instância com base no tenant
     */
    public function getInstanceForTenant($tenantId)
    {
        foreach ($this->instances as $instance) {
            if ($instance['tenant_id'] == $tenantId) {
                return $instance;
            }
        }

        // Retornar instância padrão se não encontrar
        return $this->instances['cobranca1'];
    }

    /**
     * Obter todas as instâncias disponíveis
     */
    public function getAllInstances()
    {
        return $this->instances;
    }

    /**
     * Obter instância com base na prioridade (load balancing)
     */
    public function getInstanceByPriority()
    {
        // Ordenar instâncias por prioridade e uso
        $sortedInstances = collect($this->instances)->sortBy(function ($a, $b) {
            $usageA = $this->getInstanceUsage($a['name']);
            $usageB = $this->getInstanceUsage($b['name']);

            if ($usageA['messages_per_minute'] < $usageB['messages_per_minute']) {
                return -1;
            } elseif ($usageA['messages_per_minute'] > $usageB['messages_per_minute']) {
                return 1;
            }

            return $a['priority'] - $b['priority'];
        });

        return $sortedInstances->first();
    }

    /**
     * Obter uso da instância (mensagens por minuto)
     */
    private function getInstanceUsage($instanceName)
    {
        $cacheKey = "instance_usage_{$instanceName}";
        $usage = Cache::get($cacheKey);

        if (!$usage) {
            $usage = [
                'messages_per_minute' => 0,
                'last_message_time' => null,
                'total_messages' => 0,
            ];
        }

        return $usage;
    }

    /**
     * Atualizar uso da instância
     */
    public function updateInstanceUsage($instanceName, $success)
    {
        $cacheKey = "instance_usage_{$instanceName}";
        $usage = Cache::get($cacheKey);

        if (!$usage) {
            $usage = [
                'messages_per_minute' => 0,
                'last_message_time' => null,
                'total_messages' => 0,
            ];
        }

        $now = now();
        $usage['total_messages']++;

        if ($usage['last_message_time']) {
            $minutesSinceLast = $usage['last_message_time']->diffInMinutes($now);
            $usage['messages_per_minute'] = round($usage['total_messages'] / max(1, $minutesSinceLast), 2);
        }

        $usage['last_message_time'] = $now;

        // Salvar no cache por 5 minutos
        Cache::put($cacheKey, $usage, 300);

        return $usage;
    }

    /**
     * Enviar mensagem usando a instância apropriada (load balancing)
     */
    public function sendMessage($number, $text, $tenantId = null)
    {
        // Selecionar instância baseada no tenant ou prioridade
        $instance = $tenantId
            ? $this->getInstanceForTenant($tenantId)
            : $this->getInstanceByPriority();

        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->post("{$this->baseUrl}/message/sendText/{$instance['name']}", [
                    'number' => $this->formatNumber($number),
                    'text' => $text,
                ]);

            $success = $response->successful();
            $this->updateInstanceUsage($instance['name'], $success);

            if ($success) {
                Log::info('Mensagem enviada com sucesso', [
                    'instance' => $instance['name'],
                    'tenant_id' => $instance['tenant_id'],
                    'number' => $number,
                ]);
            } else {
                Log::error('Erro ao enviar mensagem', [
                    'instance' => $instance['name'],
                    'tenant_id' => $instance['tenant_id'],
                    'number' => $number,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }

            return [
                'success' => $success,
                'instance' => $instance,
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar mensagem', [
                'instance' => $instance['name'] ?? 'unknown',
                'tenant_id' => $tenantId ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Enviar mensagem de cobrança usando load balancing
     */
    public function sendCobrancaMessage($number, $nome, $valor, $dataVencimento, $linkPagamento = null, $tenantId = null)
    {
        $message = "💰 *COBRANCA*\n\n";
        $message .= "Olá, *{$nome}*!\n\n";
        $message .= "Valor: *R$ {$valor}*\n";
        $message .= "Vencimento: *{$dataVencimento}*\n\n";

        if ($linkPagamento) {
            $message .= "🔗 Link para pagamento:\n{$linkPagamento}\n\n";
        }

        $message .= "Por favor, efetue o pagamento até a data de vencimento.";

        return $this->sendMessage($number, $message, $tenantId);
    }

    /**
     * Enviar lembrete de cobrança usando load balancing
     */
    public function sendReminderMessage($number, $nome, $valor, $dataVencimento, $tenantId = null)
    {
        $diasAtraso = now()->diffInDays($dataVencimento);
        $message = "⏰ *LEMBRETE DE COBRANCA*\n\n";
        $message .= "Olá, *{$nome}*!\n\n";
        $message .= "Você tem uma cobrança pendente:\n\n";
        $message .= "Valor: *R$ {$valor}*\n";
        $message .= "Vencimento: *{$dataVencimento}*\n";
        $message .= "Dias de atraso: *{$diasAtraso} dias*\n\n";
        $message .= "Por favor, efetue o pagamento o mais breve possível.";

        return $this->sendMessage($number, $message, $tenantId);
    }

    /**
     * Enviar confirmação de pagamento usando load balancing
     */
    public function sendPaymentConfirmation($number, $nome, $valor, $tenantId = null)
    {
        $message = "✅ *PAGAMENTO CONFIRMADO*\n\n";
        $message .= "Olá, *{$nome}*!\n\n";
        $message .= "Recebemos seu pagamento de *R$ {$valor}*\n\n";
        $message .= "Obrigado pela preferência!";

        return $this->sendMessage($number, $message, $tenantId);
    }

    /**
     * Obter status de todas as instâncias
     */
    public function getAllInstancesStatus()
    {
        $statuses = [];

        foreach ($this->instances as $instance) {
            try {
                $response = Http::withBasicAuth($this->username, $this->password)
                    ->timeout(10)
                    ->get("{$this->baseUrl}/instance/fetchInstances");

                if ($response->successful()) {
                    $instancesData = collect($response->json());
                    $instanceData = $instancesData->firstWhere('instance', $instance['name']);

                    if ($instanceData) {
                        $statuses[] = [
                            'name' => $instance['name'],
                            'tenant_id' => $instance['tenant_id'],
                            'priority' => $instance['priority'],
                            'state' => $instanceData['state'] ?? 'unknown',
                            'connected' => ($instanceData['state'] ?? '') === 'open',
                            'usage' => $this->getInstanceUsage($instance['name']),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erro ao obter status da instância', [
                    'instance' => $instance['name'],
                    'error' => $e->getMessage(),
                ]);

                $statuses[] = [
                    'name' => $instance['name'],
                    'tenant_id' => $instance['tenant_id'],
                    'priority' => $instance['priority'],
                    'state' => 'error',
                    'connected' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $statuses;
    }

    /**
     * Obter estatísticas de todas as instâncias
     */
    public function getAllInstancesStats()
    {
        $stats = [];

        foreach ($this->instances as $instance) {
            $usage = $this->getInstanceUsage($instance['name']);
            $stats[] = [
                'name' => $instance['name'],
                'tenant_id' => $instance['tenant_id'],
                'priority' => $instance['priority'],
                'messages_per_minute' => $usage['messages_per_minute'] ?? 0,
                'total_messages' => $usage['total_messages'] ?? 0,
            ];
        }

        return $stats;
    }

    /**
     * Formatar número de telefone
     */
    private function formatNumber(string $number)
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
     * Verificar se todas as instâncias estão conectadas
     */
    public function areAllInstancesConnected()
    {
        $statuses = $this->getAllInstancesStatus();

        foreach ($statuses as $status) {
            if (!$status['connected']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obter instância com menor carga
     */
    public function getLeastLoadedInstance()
    {
        $stats = $this->getAllInstancesStats();

        return collect($stats)->sortBy('messages_per_minute')->first();
    }

    /**
     * Criar nova instância
     */
    public function createInstance($instanceName, $tenantId, $priority = 1)
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->post("{$this->baseUrl}/instance/create", [
                    'instanceName' => $instanceName,
                    'qrcode' => true,
                'integration' => 'WHATSAPP-BAILEYS',
                'rejectCall' => true,
                    'msgCall' => true,
                    'groupsIgnore' => true,
                    'alwaysOnline' => true,
                    'readMessages' => true,
                    'readStatus' => true,
                ]);

            if ($response->successful()) {
                Log::info('Instância criada com sucesso', [
                    'instance' => $instanceName,
                    'tenant_id' => $tenantId,
                    'priority' => $priority,
                ]);

                // Adicionar à lista de instâncias
                $this->instances[$instanceName] = [
                    'name' => $instanceName,
                    'tenant_id' => $tenantId,
                    'priority' => $priority,
                ];

                return [
                    'success' => true,
                    'instance' => $response->json(),
                ];
            } else {
                Log::error('Erro ao criar instância', [
                    'instance' => $instanceName,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao criar instância', [
                'instance' => $instanceName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Deletar instância
     */
    public function deleteInstance($instanceName)
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->delete("{$this->baseUrl}/instance/delete/{$instanceName}");

            if ($response->successful()) {
                Log::info('Instância deletada com sucesso', [
                    'instance' => $instanceName,
                ]);

                // Remover da lista de instâncias
                unset($this->instances[$instanceName]);

                return [
                    'success' => true,
                'response' => $response->json(),
                ];
            } else {
                Log::error('Erro ao deletar instância', [
                    'instance' => $instanceName,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao deletar instância', [
                'instance' => $instanceName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Conectar instância (escanear QR)
     */
    public function connectInstance($instanceName)
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->get("{$this->baseUrl}/instance/connect/{$instanceName}");

            if ($response->successful()) {
                Log::info('Instância conectada com sucesso', [
                    'instance' => $instanceName,
                ]);

                return [
                    'success' => true,
                    'response' => $response->json(),
                ];
            } else {
                Log::error('Erro ao conectar instância', [
                    'instance' => $instanceName,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao conectar instância', [
                'instance' => $instanceName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
