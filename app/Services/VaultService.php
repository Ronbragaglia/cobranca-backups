<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VaultService
{
    private $client;
    private $token;
    private $enabled;

    public function __construct()
    {
        $this->enabled = config('vault.enabled', false);
        
        if ($this->enabled) {
            $this->token = config('vault.token');
            $this->client = new \GuzzleHttp\Client([
                'base_uri' => config('vault.addr'),
                'headers' => [
                    'X-Vault-Token' => $this->token,
                ],
            ]);
        }
    }

    public function get(string $path): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $cacheKey = "vault:{$path}";
            
            return Cache::remember($cacheKey, 3600, function () use ($path) {
                $response = $this->client->get("v1/secret/data/{$path}");
                $data = json_decode($response->getBody(), true);
                
                return $data['data']['data'] ?? null;
            });
        } catch (\Exception $e) {
            Log::error('Vault error: ' . $e->getMessage());
            return null;
        }
    }

    public function getStripeSecrets(): ?array
    {
        return $this->get('stripe');
    }

    public function getEvolutionSecrets(): ?array
    {
        return $this->get('evolution');
    }

    public function getDatabaseSecrets(): ?array
    {
        return $this->get('database');
    }

    public function getRedisSecrets(): ?array
    {
        return $this->get('redis');
    }

    public function getAppSecrets(): ?array
    {
        return $this->get('app');
    }

    public function get(string $key, string $default = null): ?string
    {
        $secrets = $this->get($key);
        
        if (!$secrets) {
            return $default;
        }

        return $secrets[$key] ?? $default;
    }

    public function refreshCache(): void
    {
        Cache::forget('vault:stripe');
        Cache::forget('vault:evolution');
        Cache::forget('vault:database');
        Cache::forget('vault:redis');
        Cache::forget('vault:app');
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
