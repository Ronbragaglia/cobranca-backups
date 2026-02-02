# Cobrança API

API REST em Laravel para gerenciar cobranças (CRUD) com autenticação via token (Laravel Sanctum).

## Requisitos
- Docker + Docker Compose
- PHP/Composer (se for rodar comandos fora do Sail)

## Subir o projeto (Laravel Sail)
1) Instalar dependências:
   ```bash
   composer install 
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan route:list
Endpoints principais
GET /api/status

POST /api/login

GET|POST|PUT|DELETE /api/cobrancas (protegido por autenticação)

Testes rápidos (curl)
1) Status
bash
curl -i http://localhost/api/status
2) Login (retorna token)
Se o seu endpoint aceitar JSON:

bash
curl -i -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
Se o seu endpoint estiver usando form-url-encoded:

bash
curl -i -X POST http://localhost/api/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=user@example.com&password=password"
3) Criar cobrança (Bearer token)
O token deve ser enviado no header Authorization como Bearer.

bash
curl -i -X POST http://localhost/api/cobrancas \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{"descricao":"Teste","valor":100,"status":"pendente"}'

## Multi-Tenant Setup

Este projeto suporta multi-tenancy baseado em subdomínios.

### Comandos de Setup
1. Subir containers:
   ```bash
   ./vendor/bin/sail up -d
   ```

2. Rodar migrations:
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

3. Rodar seeders (cria tenants Principal e Demo):
   ```bash
   ./vendor/bin/sail artisan db:seed --class=TenantSeeder
   ```

4. Verificar status das migrations:
   ```bash
   ./vendor/bin/sail artisan migrate:status
   ```

### Teste de Demo Tenant Local
Para testar o tenant demo localmente:

1. Editar `/etc/hosts` (Linux/Mac) ou `C:\Windows\System32\drivers\etc\hosts` (Windows):
   ```
   127.0.0.1 demo.localhost
   ```

2. Acessar no navegador:
   - Principal: http://localhost
   - Demo: http://demo.localhost

3. Login com credenciais:
   - Admin Principal: admin@seucrm.com / password
   - Admin Demo: demo@seucrm.com / password

### Gerenciamento de Tenants
- Rota para listar tenants: GET /tenants (protegida por auth)
- Criar tenant: GET /tenants/create e POST /tenants (protegida por auth)
- Criar tenant via seeder ou manualmente no banco

## Stripe Integration
Este projeto integra com Stripe para gerenciar CustomerIDs por tenant.

### Setup Stripe
1. Instalar dependências:
   ```bash
   composer require stripe/stripe-php
   ```

2. Configurar chaves no `.env`:
   ```
   STRIPE_KEY=pk_test_...
   STRIPE_SECRET=sk_test_...
   ```

3. Ao criar um tenant, um Customer é automaticamente criado no Stripe e o ID é armazenado no tenant.

## Dashboard MRR e Churn
O painel admin (/painel) agora exibe:
- **MRR (Monthly Recurring Revenue)**: Soma dos valores das cobranças com status 'pago'.
- **Churn Rate**: Porcentagem de tenants com subscription_status != 'active'.

### Notas
- MRR assume cobranças pagas como recorrentes mensais.
- Churn é calculado baseado no campo subscription_status do tenant.

## Configuração de Domínio

### Compra do Domínio
- Domínio: cobrancaauto.com.br
- Registrado em: Registro.br
- Data de compra: [Inserir data da compra]

### Configuração DNS (Cloudflare)
- A record @ → 76.13.167.54 (TTL 300) - Domínio principal
- A record app → 76.13.167.54 (TTL 300) - Aplicação frontend
- A record api → 76.13.167.54 (TTL 300) - API REST
- CNAME www → cobrancaauto.com.br (TTL 300) - WWW

### VPS Hostinger
- IP Público: 76.13.167.54
- Servidor: srv1298946
- Sistema Operacional: Ubuntu 22.04

### Caddyfile
O Caddyfile está configurado para o domínio cobrancaauto.com.br, localizado na raiz do projeto.
Ele configura HTTPS automático com Let's Encrypt e inclui headers de segurança.
