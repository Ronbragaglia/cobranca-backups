# AUDITORIA DE SEGURANÇA COMPLETA - CobrançaAuto SaaS

**Data:** 2026-02-02
**Status:** CRÍTICO - Deploy BLOQUEADO
**Prioridade:** MÁXIMA

---

## RESUMO EXECUTIVO

A auditoria identificou **VIOLAÇÕES CRÍTICAS DE SEGURANÇA** que impedem o deploy em produção. O projeto expõe:

- **1 TOKEN REAL** da Cloudflare
- **19 ocorrências** da senha SSH root
- **Múltiplas senhas** de banco de dados em texto
- **1 senha** de email do Resend
- **1 chave SSH pública** em vários arquivos
- **Containers Docker** rodando como root
- **Sem configuração** de TRUSTED_PROXIES para Cloudflare
- **phpMyAdmin** exposto em produção

---

## 1. SEGREDOS EXPOSTOS (CRÍTICO)

### 1.1 TOKEN CLOUDFLARE REAL - URGENTE ⚠️

**Arquivos afetados:**
- [ ] [`.env`](.env:91) - Linha 91
- [ ] [`.env.production.example`](.env.production.example:115) - Linha 115

**Problema:**
```env
CLOUDFLARE_API_TOKEN=__SET_IN_SERVER_ONLY__
```

**Risco:**
- **CRÍTICO** - Token real exposto no código
- Permite acesso completo à conta Cloudflare
- Pode ser usado para: DNS, WAF, SSL, configurações de segurança
- **Ação imediata:** Revogar este token na Cloudflare AGORA

**Correção necessária:**
```env
CLOUDFLARE_API_TOKEN=your_cloudflare_api_token_here
```

---

### 1.2 SENHA SSH ROOT - 19 OCORRÊNCIAS ⚠️

**Arquivos afetados:**
- [ ] [`CONTINUAR_DEPLOY_AMANHA.txt`](CONTINUAR_DEPLOY_AMANHA.txt:36) - Linha 36
- [ ] [`scripts/executar-deploy-completo.sh`](scripts/executar-deploy-completo.sh:6,19) - Linhas 6, 19
- [ ] [`scripts/deploy-vps-com-senha.sh`](scripts/deploy-vps-com-senha.sh:6,19) - Linhas 6, 19
- [ ] [`scripts/deploy-vps-expect.sh`](scripts/deploy-vps-expect.sh:6,14) - Linhas 6, 14
- [ ] [`scripts/configurar_ssh_manual.sh`](scripts/configurar_ssh_manual.sh:6) - Linha 6
- [ ] [`scripts/configurar_ssh_vps.py`](scripts/configurar_ssh_vps.py:9) - Linha 9
- [ ] [`scripts/setup-ssh-e-deploy-completo.sh`](scripts/setup-ssh-e-deploy-completo.sh:6,19) - Linhas 6, 19
- [ ] [`GUIA_DEPLOY_COMPLETO.txt`](GUIA_DEPLOY_COMPLETO.txt:6,22) - Linhas 6, 22
- [ ] [`RELATORIO_PROGRESSO_DEPLOY_VPS.md`](RELATORIO_PROGRESSO_DEPLOY_VPS.md:20,70,156) - Linhas 20, 70, 156
- [ ] [`INSTRUCOES_FINAIS_DEPLOY.txt`](INSTRUCOES_FINAIS_DEPLOY.txt:6,18,23) - Linhas 6, 18, 23

**Problema:**
```bash
Senha SSH: 1Qaz2wsx@2026
VPS_PASSWORD="1Qaz2wsx@2026"
```

**Risco:**
- **CRÍTICO** - Senha root exposta em 19 arquivos
- Permite acesso completo à VPS
- Pode estar no histórico de git
- **Ação imediata:** Trocar senha root da VPS AGORA

**Correção necessária:**
- Remover todas as ocorrências
- Criar usuário não-root: `cobranca-deploy`
- Usar apenas autenticação por chave SSH
- Desabilitar PasswordAuthentication

---

### 1.3 SENHAS DE BANCO DE DADOS ⚠️

**Arquivos afetados:**

#### Senha `Cobranca@2024!Secure` (Produção)
- [ ] [`.env`](.env:19) - Linha 19
- [ ] [`.env.production.example`](.env.production.example:23) - Linha 23
- [ ] [`docker-compose.prod.yml`](docker-compose.prod.yml:12,35,78,98) - Linhas 12, 35, 78, 98
- [ ] [`SCRIPT_VPS_ETAPA2_APP.sh`](SCRIPT_VPS_ETAPA2_APP.sh:47,218) - Linhas 47, 218
- [ ] [`docs/CONFIGURACAO_PRODUCAO_CLOUDFLARE.md`](docs/CONFIGURACAO_PRODUCAO_CLOUDFLARE.md:86) - Linha 86
- [ ] [`RELATORIO_PADRONIZACAO_AMBIENTE.md`](RELATORIO_PADRONIZACAO_AMBIENTE.md:224) - Linha 224

**Problema:**
```env
DB_PASSWORD=__SET_IN_SERVER_ONLY__
MYSQL_PASSWORD=__SET_IN_SERVER_ONLY__
```

#### Senha `cobranca` (Desenvolvimento)
- [ ] [`.env.local`](.env.local:19) - Linha 19
- [ ] [`docker-compose.dev.yml`](docker-compose.dev.yml:22,51) - Linhas 22, 51
- [ ] [`scripts/start-dev.sh`](scripts/start-dev.sh:44) - Linha 44
- [ ] [`setup-mysql-docker.sh`](setup-mysql-docker.sh:40,66) - Linhas 40, 66
- [ ] [`README_MYSQL_DOCKER.md`](README_MYSQL_DOCKER.md:64) - Linha 64

**Problema:**
```env
DB_PASSWORD=__SET_IN_SERVER_ONLY__
MYSQL_PASSWORD=__SET_IN_SERVER_ONLY__
```

**Risco:**
- **ALTO** - Senhas de banco expostas em 10+ arquivos
- Permite acesso completo ao banco de dados
- Senha de dev (`cobranca`) é extremamente fraca
- **Ação imediata:** Gerar novas senhas fortes

**Correção necessária:**
```env
# .env.production.example
DB_PASSWORD=your_secure_password_here

# docker-compose.prod.yml
MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
MYSQL_PASSWORD: ${MYSQL_PASSWORD}
```

---

### 1.4 SENHA DE EMAIL RESEND ⚠️

**Arquivos afetados:**
- [ ] [`.env`](.env:66) - Linha 66
- [ ] [`.env.production.example`](.env.production.example:82) - Linha 82

**Problema:**
```env
MAIL_PASSWORD=re_XYZ123456789
```

**Risco:**
- **ALTO** - Token de API do Resend exposto
- Permite envio de emails em nome da conta
- Pode ser usado para spam/phishing
- **Ação imediata:** Revogar este token no Resend AGORA

**Correção necessária:**
```env
MAIL_PASSWORD=your_resend_api_key_here
```

---

### 1.5 CHAVE SSH PÚBLICA EXPOSTA ⚠️

**Arquivos afetados:**
- [ ] [`CONTINUAR_DEPLOY_AMANHA.txt`](CONTINUAR_DEPLOY_AMANHA.txt:34) - Linha 34
- [ ] [`GUIA_DEPLOY_COMPLETO.txt`](GUIA_DEPLOY_COMPLETO.txt:12,20) - Linhas 12, 20
- [ ] [`RELATORIO_PROGRESSO_DEPLOY_VPS.md`](RELATORIO_PROGRESSO_DEPLOY_VPS.md:30,67,154) - Linhas 30, 67, 154
- [ ] [`INSTRUCOES_FINAIS_DEPLOY.txt`](INSTRUCOES_FINAIS_DEPLOY.txt:20) - Linha 20
- [ ] [`scripts/executar-deploy-completo.sh`](scripts/executar-deploy-completo.sh:36) - Linha 36

**Problema:**
```bash
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDEOcC7bXcpN9NszSVCnHmmrXktf2yyALI+VGnMd6eGgmaA9uBz3KhR838HqcatX7YNPp40tHPhooxys71mfVaRA6DmrHcgwwAF9Hm0L7GM7HHW90vWI11+wzonebj8R17+rVWbg2VBgSI5wNiHmaYxRvVf/hBgJ4hOmUpC9OSi46btTbwRHekY2AO7hqBHqGh6m7xoy0Bx/Leuq40EvlCoiOnkE6aklHnILPI4gqmkDoNN33cacTBnMnb1gSc14yd9xQh3n8wP7LtG7JKD/BQgwMLLHTEcaS1/rg/turPNhcKrRV/ZSjb+P1Tzc06yO7FQUHGwIimq1FHyRJETiN81Wt3XNsoUqF9oD2YJMmQCo2mptBbvVa8HodMyE0zVl3/WQzvZt3k+NVIZoIw0Kn6tRbfiSNRjkHBOfrf20UvB7yAlWotf9/c1x43B8z0lhgWJtF4AHSX1Sh8i [truncated...]
```

**Risco:**
- **MÉDIO** - Chave pública exposta em 5 arquivos
- Permite identificar o usuário associado
- Embora seja apenas a chave pública, não deveria estar no código
- **Ação recomendada:** Remover do código, usar variáveis de ambiente

**Correção necessária:**
- Remover chaves SSH dos arquivos
- Usar variáveis de ambiente ou secrets do GitHub Actions
- Documentar processo de adição de chaves SSH

---

## 2. DOCKER COMPOSE - PROBLEMAS

### 2.1 docker-compose.dev.yml

**Problemas encontrados:**

1. **Senhas fracas expostas:**
```yaml
MYSQL_ROOT_PASSWORD: __SET_IN_SERVER_ONLY__
MYSQL_PASSWORD: __SET_IN_SERVER_ONLY__
PMA_PASSWORD: __SET_IN_SERVER_ONLY__
```

2. **Portas expostas sem justificativa:**
```yaml
ports:
  - "8000:80"    # Nginx - OK
  - "3306:3306"  # MySQL - PERIGO! Expor DB diretamente
  - "8080:80"    # phpMyAdmin - PERIGO! Ferramenta de admin exposta
```

3. **Sem user: definido** - Containers rodando como root
4. **phpMyAdmin exposto** - Ferramenta de admin não deveria estar em dev

**Correções necessárias:**
```yaml
# Remover porta 3306 (não expor DB)
# Remover phpMyAdmin ou usar apenas localmente
# Adicionar user: www-data
mysql:
  user: mysql:mysql
  # ports: - REMOVER

app:
  user: www-data:www-data
```

---

### 2.2 docker-compose.prod.yml

**Problemas encontrados:**

1. **Senhas expostas no arquivo:**
```yaml
MYSQL_ROOT_PASSWORD: __SET_IN_SERVER_ONLY__
MYSQL_PASSWORD: __SET_IN_SERVER_ONLY__
DB_PASSWORD: __SET_IN_SERVER_ONLY__
```

2. **Healthcheck com senha incorreta:**
```yaml
healthcheck:
  test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p__SET_IN_SERVER_ONLY__"]
# Deveria ser: "-p__SET_IN_SERVER_ONLY__"
```

3. **Sem user: definido** - Containers rodando como root
4. **Sem limites de recursos** - CPU, memória não limitados

**Correções necessárias:**
```yaml
# Usar variáveis de ambiente
environment:
  MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
  MYSQL_PASSWORD: ${MYSQL_PASSWORD}
  DB_PASSWORD: ${DB_PASSWORD}

# Adicionar user
app:
  user: www-data:www-data

# Corrigir healthcheck
healthcheck:
  test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p__SET_IN_SERVER_ONLY__"]

# Adicionar limites de recursos
deploy:
  resources:
    limits:
      cpus: '1'
      memory: 1G
    reservations:
      cpus: '0.5'
      memory: 512M
```

---

## 3. LARAVEL - CONFIGURAÇÕES DE PRODUÇÃO

### 3.1 APP_DEBUG ✅ OK

**Status:** Configurado corretamente
```php
'debug' => (bool) env('APP_DEBUG', false),
```

---

### 3.2 TRUSTED_PROXIES ❌ NÃO CONFIGURADO

**Problema:** Não há configuração de TRUSTED_PROXIES para Cloudflare

**Risco:**
- **ALTO** - IPs de usuários não são confiáveis
- `request()->ip()` retorna IP do proxy, não do usuário real
- Rate limiting por IP não funciona corretamente
- Validação de webhooks pode falhar

**Correção necessária:**

Criar `app/Http/Middleware/TrustProxies.php`:
```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string|null
     */
    protected $proxies = '*'; // Ou IPs específicos do Cloudflare

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
```

Registrar em `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\TrustProxies::class);
})
```

**IPs do Cloudflare para whitelist:**
```
https://www.cloudflare.com/ips-v4
https://www.cloudflare.com/ips-v6
```

---

### 3.3 RATE LIMITING ✅ PARCIALMENTE OK

**Status:** Configurado, mas pode ser melhorado

**Encontrado:**
- `AdvancedRateLimitMiddleware` customizado
- Rate limit padrão do Laravel em rotas de auth
- Rate limit por API key em `PublicApiController`

**Recomendações:**
- Configurar rate limit global em `routes/api.php`
- Usar Redis para rate limiting em produção
- Adicionar rate limit por IP para endpoints públicos

---

### 3.4 CSRF ✅ OK

**Status:** Configurado corretamente por padrão no Laravel

---

### 3.5 CORS ❌ NÃO CONFIGURADO

**Problema:** Não há arquivo `config/cors.php`

**Risco:**
- **MÉDIO** - Endpoints API podem ter problemas de CORS
- Aplicações frontend podem não conseguir acessar a API

**Correção necessária:**

Criar `config/cors.php`:
```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Em produção, especificar domínios
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

---

## 4. SSH E ACESSO VPS

### 4.1 Scripts de Deploy - INSEGUROS ⚠️

**Problemas encontrados:**

1. **Usam senha em texto:**
```bash
VPS_PASSWORD="1Qaz2wsx@2026"
sshpass -p "$VPS_PASSWORD" ssh ...
```

2. **Desabilitam verificação de host:**
```bash
-o StrictHostKeyChecking=no
```

3. **Usam usuário root:**
```bash
VPS_USER="root"
```

**Risco:**
- **CRÍTICO** - Autenticação por senha é insegura
- `StrictHostKeyChecking=no` permite ataques MITM
- Usuário root tem acesso total ao sistema

**Correção necessária:**

1. **Criar usuário não-root:**
```bash
# Na VPS
adduser cobranca-deploy
usermod -aG sudo cobranca-deploy
visudo  # Adicionar: cobranca-deploy ALL=(ALL) NOPASSWD:/usr/bin/docker
```

2. **Configurar SSH para chaves:**
```bash
# /etc/ssh/sshd_config
PermitRootLogin prohibit-password
PasswordAuthentication no
PubkeyAuthentication yes
AllowUsers cobranca-deploy
```

3. **Atualizar scripts para usar chaves:**
```bash
# Remover sshpass
# Usar chave SSH configurada
ssh -i ~/.ssh/cobranca_deploy cobranca-deploy@76.13.167.54 "command"
```

---

## 5. LOGS E ROTAÇÃO

### 5.1 Configuração de Logs ✅ OK

**Status:** Configurado corretamente

```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => env('LOG_DAILY_DAYS', 14),
    'replace_placeholders' => true,
],
```

**Recomendações:**
- Em produção, usar `LOG_LEVEL=error` ou `warning`
- Aumentar `LOG_DAILY_DAYS` para 30 dias
- Configurar log aggregation (Sentry, LogRocket, etc.)

---

### 5.2 Dados Sensíveis em Logs ⚠️

**Problema:** Não há configuração para remover dados sensíveis de logs

**Correção necessária:**

Adicionar em `app/Providers/AppServiceProvider.php`:
```php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

public function boot()
{
    // Remover dados sensíveis de logs
    Log::shareContext([
        'request' => function ($request) {
            return [
                'ip' => $request->ip(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                // NÃO logar: password, token, api_key, etc.
            ];
        },
    ]);
}
```

---

## 6. CLOUDFLARE E PROXIES

### 6.1 Configuração de Domínios ❌ NÃO DOCUMENTADO

**Problema:** Não há tabela documentando domínio → serviço → proxy status

**Correção necessária:**

Criar documentação em `docs/CLOUDFLARE_DOMINIOS.md`:
```markdown
# Domínios Cloudflare - CobrançaAuto

| Domínio | Serviço | Proxy SSL | WAF | Rate Limiting | Status |
|---------|----------|------------|-----|---------------|---------|
| cobrancaauto.com.br | App Laravel | ✅ Full (Strict) | ✅ High | ✅ 100 req/min | ✅ Ativo |
| api.cobrancaauto.com.br | API Laravel | ✅ Full (Strict) | ✅ High | ✅ 1000 req/min | ✅ Ativo |
| app.cobrancaauto.com.br | App Laravel | ✅ Full (Strict) | ✅ High | ✅ 100 req/min | ✅ Ativo |
| www.cobrancaauto.com.br | Redirect → cobrancaauto.com.br | ✅ Full (Strict) | ✅ High | ✅ 100 req/min | ✅ Ativo |

## Configurações Obrigatórias

### SSL/TLS
- Mode: Full (Strict)
- Always Use HTTPS: ✅ ON
- Automatic HTTPS Rewrites: ✅ ON

### Security
- Security Level: High
- Bot Fight Mode: ✅ ON
- Challenge Passage: 30 minutos

### Performance
- Auto Minify: ✅ ON (JS, CSS, HTML)
- Brotli: ✅ ON
- Rocket Loader: ⚠️ OFF (pode quebrar JS)
```

---

## 7. BLOQUEADORES PARA PRODUÇÃO

### CRÍTICOS (impedem deploy)

- [ ] **TOKEN CLOUDFLARE REAL** exposto em `.env` e `.env.production.example`
  - **Ação:** Revogar token na Cloudflare AGORA
  - **Arquivos:** `.env:91`, `.env.production.example:115`

- [ ] **SENHA SSH ROOT** exposta em 19 arquivos
  - **Ação:** Trocar senha root da VPS AGORA
  - **Arquivos:** Lista completa acima

- [ ] **SENHAS DE BANCO** expostas em texto
  - **Ação:** Gerar novas senhas fortes
  - **Arquivos:** `.env:19`, `.env.production.example:23`, `docker-compose.prod.yml`

- [ ] **SENHA EMAIL RESEND** exposta
  - **Ação:** Revogar token no Resend AGORA
  - **Arquivos:** `.env:66`, `.env.production.example:82`

- [ ] **CONTAINERS DOCKER** rodando como root
  - **Ação:** Adicionar `user:` em todos os serviços
  - **Arquivos:** `docker-compose.dev.yml`, `docker-compose.prod.yml`

- [ ] **TRUSTED_PROXIES** não configurado para Cloudflare
  - **Ação:** Criar middleware e registrar
  - **Arquivos:** Criar `app/Http/Middleware/TrustProxies.php`

- [ ] **phpMyAdmin** exposto em produção
  - **Ação:** Remover ou proteger com autenticação forte
  - **Arquivos:** `docker-compose.dev.yml`

### IMPORTANTES (corrigir em 24h)

- [ ] **Rate limiting** não configurado para produção
  - **Ação:** Configurar rate limit global em `routes/api.php`

- [ ] **CORS** não configurado
  - **Ação:** Criar `config/cors.php`

- [ ] **LOG_LEVEL** em debug
  - **Ação:** Usar `LOG_LEVEL=error` em produção

- [ ] **Healthcheck** com senha incorreta
  - **Ação:** Corrigir em `docker-compose.prod.yml`

---

## 8. PRÓXIMOS PASSOS SEGUROS

### FASE 1: CORREÇÕES IMEDIATAS (HOJE)

1. **Revogar token Cloudflare:**
   ```bash
   # Acessar painel Cloudflare
   # Revogar token: l7EN2FvAklLm0bfXLT-unMQ3mwMO8vUm73JroUpI
   # Gerar novo token com permissões mínimas
   ```

2. **Trocar senha root VPS:**
   ```bash
   # Na VPS
   passwd root  # Digitar nova senha forte
   ```

3. **Revogar token Resend:**
   ```bash
   # Acessar painel Resend
   # Revogar token: re_XYZ123456789
   # Gerar novo token
   ```

4. **Remover segredos dos arquivos:**
   - Substituir por placeholders em todos os arquivos listados
   - Commitar mudanças
   - Limpar histórico do git (git filter-branch ou BFG)

### FASE 2: CONFIGURAÇÃO SSH (HOJE)

1. **Criar usuário não-root:**
   ```bash
   adduser cobranca-deploy
   usermod -aG sudo cobranca-deploy
   ```

2. **Configurar SSH para chaves:**
   ```bash
   # /etc/ssh/sshd_config
   PermitRootLogin prohibit-password
   PasswordAuthentication no
   PubkeyAuthentication yes
   AllowUsers cobranca-deploy
   systemctl restart sshd
   ```

3. **Gerar chaves SSH:**
   ```bash
   ssh-keygen -t ed25519 -C "cobranca-deploy" -f ~/.ssh/cobranca_deploy
   ```

4. **Atualizar scripts de deploy:**
   - Remover `sshpass`
   - Usar chave SSH
   - Mudar usuário para `cobranca-deploy`

### FASE 3: DOCKER HARDENING (AMANHÃ)

1. **Adicionar user: aos containers:**
   ```yaml
   app:
     user: www-data:www-data
   
   mysql:
     user: mysql:mysql
   ```

2. **Remover portas desnecessárias:**
   - Remover `3306:3306` de docker-compose.dev.yml
   - Remover phpMyAdmin ou proteger com autenticação

3. **Adicionar limites de recursos:**
   ```yaml
   deploy:
     resources:
       limits:
         cpus: '1'
         memory: 1G
   ```

4. **Usar variáveis de ambiente para senhas:**
   ```yaml
   environment:
     MYSQL_PASSWORD: ${MYSQL_PASSWORD}
   ```

### FASE 4: LARAVEL PRODUCTION (AMANHÃ)

1. **Criar TrustProxies middleware:**
   - Criar `app/Http/Middleware/TrustProxies.php`
   - Configurar IPs do Cloudflare
   - Registrar em `bootstrap/app.php`

2. **Configurar CORS:**
   - Criar `config/cors.php`
   - Especificar domínios permitidos em produção

3. **Configurar rate limiting:**
   - Adicionar rate limit global em `routes/api.php`
   - Usar Redis para rate limiting

4. **Ajustar nível de logs:**
   ```env
   LOG_LEVEL=error
   LOG_DAILY_DAYS=30
   ```

### FASE 5: CLOUDFLARE DOCUMENTAÇÃO (AMANHÃ)

1. **Documentar domínios:**
   - Criar `docs/CLOUDFLARE_DOMINIOS.md`
   - Listar todos os domínios e serviços

2. **Validar configurações:**
   - SSL/TLS: Full (Strict)
   - WAF: High
   - Rate Limiting: Configurado

3. **Testar proxies:**
   - Validar que nenhum código depende de IP direto
   - Usar apenas `X-Forwarded-For`

### FASE 6: MONITORAMENTO E BACKUP (DEPOIS DO DEPLOY)

1. **Configurar monitoramento:**
   - UptimeRobot para uptime
   - Sentry para erros
   - LogRocket para sessões

2. **Configurar backups:**
   - Backup diário do banco
   - Backup semanal de arquivos
   - Testar restore mensalmente

3. **Configurar alertas:**
   - Email para erros críticos
   - Slack para alertas de segurança
   - SMS para downtime

---

## 9. CHECKLIST DE DEPLOY SEGURO

### Pré-Deploy

- [ ] Todos os segredos removidos do código
- [ ] Novos tokens/senhas gerados e armazenados em .env
- [ ] .env adicionado ao .gitignore
- [ ] SSH configurado com chaves (sem senha)
- [ ] Usuário não-root criado na VPS
- [ ] Containers Docker não rodam como root
- [ ] TRUSTED_PROXIES configurado para Cloudflare
- [ ] Rate limiting ativo
- [ ] CORS configurado
- [ ] APP_DEBUG=false
- [ ] LOG_LEVEL=error
- [ ] SSL válido configurado
- [ ] Firewall configurado (apenas 22, 80, 443)
- [ ] phpMyAdmin removido ou protegido
- [ ] Backups agendados
- [ ] Monitoramento ativo

### Pós-Deploy

- [ ] Testar endpoints críticos
- [ ] Verificar logs de erro
- [ ] Testar webhooks
- [ ] Validar monitoramento
- [ ] Testar rate limiting
- [ ] Validar TRUSTED_PROXIES
- [ ] Documentar mudanças

---

## 10. CONTATO E ESCALADA

### Emergência de Segurança

**Responsável:** [NOME]
**Email:** [EMAIL]
**Telefone:** [TELEFONE]

### Procedimento de Incidente

1. **Identificar e conter** - Isolar sistema afetado
2. **Notificar stakeholders** - Informar equipe e clientes
3. **Investigar causa raiz** - Analisar logs e evidências
4. **Implementar correção** - Aplicar patch/fix
5. **Documentar lições aprendidas** - Criar post-mortem

---

## CONCLUSÃO

**Status:** DEPLOY BLOQUEADO

**Motivo:** Violações críticas de segurança que devem ser corrigidas antes do deploy

**Tempo estimado para correção:** 2-3 dias

**Próxima ação:** Executar FASE 1 (Correções Imediatas) HOJE

---

**RELATÓRIO GERADO:** 2026-02-02
**VERSÃO:** 1.0
**AUDITOR:** Kilo Code
