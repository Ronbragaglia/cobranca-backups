# ROADMAP TÉCNICO - COBRANÇA API

## 📋 VISÃO GERAL

Este roadmap organiza as features e melhorias do projeto em fases lógicas baseadas em prioridade técnica e racional de sobrevivência do produto. A ordem é **obrigatória** para garantir estabilidade e segurança antes de escalar.

---

## 🚨 FASE 0: PRÉ-PRODUÇÃO OBRIGATÓRIA

**RACIONAL TÉCNICO:** Antes de qualquer funcionalidade de produto ou escala, precisamos garantir segurança mínima e observabilidade. Sem isso, ficamos cegos e vulneráveis.

### 0.1 Segurança Mínima Obrigatória

#### Rate Limiting por IP, Tenant e Endpoint
- **Status:** ⚠️ PENDENTE
- **Prioridade:** CRÍTICA
- **Implementação:**
  - Laravel throttle + Redis
  - Rate limiting por IP: 100 req/min
  - Rate limiting por tenant: 1000 req/min (evita cliente matar o sistema sozinho)
  - Rate limiting por usuário autenticado: 500 req/min
  - Limites separados para endpoints críticos:
    - Login: 10 req/min
    - Webhook: 100 req/min
    - Envio WhatsApp: 20 req/min
- **Arquivos afetados:**
  - [`routes/api.php`](routes/api.php)
  - [`config/throttle.php`](config/throttle.php)
  - [`app/Http/Kernel.php`](app/Http/Kernel.php)
  - [`app/Http/Middleware/AdvancedRateLimitMiddleware.php`](app/Http/Middleware/AdvancedRateLimitMiddleware.php)
- **Racional:** Protege contra ataques DDoS, brute force, abuso de API e evita que um único cliente sobrecarregue o sistema

#### Verificação de E-mail Obrigatória
- **Status:** ⚠️ PENDENTE
- **Prioridade:** CRÍTICA
- **Implementação:**
  - Bloquear envio de cobranças reais sem e-mail verificado
  - Middleware para verificar email_verified_at
  - Notificação clara no dashboard se e-mail não verificado
  - Reenvio de e-mail de verificação
- **Arquivos afetados:**
  - [`app/Http/Middleware/EnsureEmailIsVerified.php`](app/Http/Middleware/EnsureEmailIsVerified.php)
  - [`app/Http/Controllers/CobrancaController.php`](app/Http/Controllers/CobrancaController.php)
  - [`app/Models/User.php`](app/Models/User.php)
- **Racional:** Evita envio de cobranças para e-mails inválidos/fake, protege reputação de domínio

#### Proteção CSRF e Headers de Segurança
- **Status:** ✅ IMPLEMENTADO
- **Prioridade:** CRÍTICA
- **Implementação:**
  - CSP (Content Security Policy) configurado
  - HSTS habilitado
  - X-Frame-Options, X-Content-Type-Options configurados
- **Arquivos afetados:**
  - [`app/Http/Middleware/SecurityMiddleware.php`](app/Http/Middleware/SecurityMiddleware.php)
  - [`config/cors.php`](config/cors.php)
- **Racional:** Protege contra XSS, clickjacking e outros ataques web

#### CAPTCHA em Formulários Públicos
- **Status:** ⚠️ PENDENTE
- **Prioridade:** ALTA
- **Implementação:**
  - CAPTCHA apenas em formulários públicos (registro, reset de senha)
  - NÃO implementar CAPTCHA em login interno
  - Usar reCAPTCHA v3 ou hCaptcha
- **Arquivos afetados:**
  - [`app/Http/Controllers/Auth/RegisteredUserController.php`](app/Http/Controllers/Auth/RegisteredUserController.php)
  - [`app/Http/Controllers/Auth/NewPasswordController.php`](app/Http/Controllers/Auth/NewPasswordController.php)
- **Racional:** Protege contra spam e criação de contas automatizada sem afetar UX de usuários reais

### 0.2 Observabilidade Antes de Performance

#### Logs Estruturados com Correlation ID
- **Status:** ⚠️ PARCIALMENTE IMPLEMENTADO
- **Prioridade:** CRÍTICA
- **Implementação:**
  - Logs estruturados em JSON
  - **Correlation ID padrão em todos os logs** (salva horas no futuro quando WhatsApp, pagamento e webhook se cruzam)
  - Canal separado para pagamentos (stripe, pix)
  - Canal separado para WhatsApp (evolution api)
  - Canal separado para autenticação
  - Canal separado para erros críticos
  - Correlation ID propagado entre jobs, filas e webhooks
- **Arquivos afetados:**
  - [`config/logging.php`](config/logging.php)
  - [`app/Logging/StructuredLogger.php`](app/Logging/StructuredLogger.php)
  - [`app/Middleware/CorrelationIdMiddleware.php`](app/Middleware/CorrelationIdMiddleware.php)
  - [`app/Jobs/`](app/Jobs/)
- **Racional:** Sem logs estruturados e correlation ID, ficamos cegos em produção. Impossível debugar problemas que cruzam múltiplos serviços.

#### Error Tracking (Sentry)
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** CRÍTICA
- **Implementação:**
  - Integração com Sentry ou similar (Bugsnag, Rollbar)
  - Captura automática de exceções
  - Contexto de usuário e request
  - Alertas em tempo real
- **Arquivos afetados:**
  - [`composer.json`](composer.json)
  - [`config/sentry.php`](config/sentry.php)
  - [`app/Providers/AppServiceProvider.php`](app/Providers/AppServiceProvider.php)
- **Racional:** Error tracking proativo permite identificar e corrigir bugs antes que afetem muitos usuários

#### Healthcheck Endpoint
- **Status:** ⚠️ PARCIALMENTE IMPLEMENTADO
- **Prioridade:** CRÍTICA
- **Implementação:**
  - Endpoint `/health` simples
  - Verifica: conexão com banco, Redis, filas
  - Retorna status 200 se tudo OK, 503 se problema
  - Usado por UptimeRobot e outros monitors
- **Arquivos afetados:**
  - [`routes/api.php`](routes/api.php)
  - [`app/Http/Controllers/HealthController.php`](app/Http/Controllers/HealthController.php)
- **Racional:** Monitoramento básico de uptime. Sem isso, não sabemos quando o serviço cai.

---

## 🛡️ FASE 1: SOBREVIVÊNCIA DO PRODUTO

**RACIONAL TÉCNICO:** Após segurança e observabilidade, focamos em features essenciais para o produto sobreviver e entregar valor mínimo viável.

### 1.1 Core de Cobranças

#### CRUD de Cobranças
- **Status:** ✅ IMPLEMENTADO
- **Prioridade:** ESSENCIAL
- **Funcionalidades:**
  - Criar, ler, atualizar, deletar cobranças
  - Status: pendente, pago, atrasado, cancelado
  - Validação de dados
- **Arquivos:**
  - [`app/Http/Controllers/CobrancaController.php`](app/Http/Controllers/CobrancaController.php)
  - [`app/Models/Cobranca.php`](app/Models/Cobranca.php)

#### Envio de Cobranças por E-mail
- **Status:** ✅ IMPLEMENTADO
- **Prioridade:** ESSENCIAL
- **Funcionalidades:**
  - Template de e-mail profissional
  - Link de pagamento (Stripe/Pix)
  - Rastreamento de abertura e clique
- **Arquivos:**
  - [`app/Jobs/SendReminderEmail.php`](app/Jobs/SendReminderEmail.php)
  - [`resources/views/emails/`](resources/views/emails/)

#### Envio de Cobranças por WhatsApp
- **Status:** ✅ IMPLEMENTADO
- **Prioridade:** ESSENCIAL
- **Funcionalidades:**
  - Integração com Evolution API
  - Template de mensagem
  - Rastreamento de entrega
- **Arquivos:**
  - [`app/Jobs/SendWhatsAppReminder.php`](app/Jobs/SendWhatsAppReminder.php)
  - [`app/Http/Controllers/WhatsAppReminderSettingsController.php`](app/Http/Controllers/WhatsAppReminderSettingsController.php)

### 1.2 Automações de Lembretes

#### Lembretes de Vencimento
- **Status:** ✅ IMPLEMENTADO
- **Prioridade:** ESSENCIAL
- **Funcionalidades:**
  - Job agendado para enviar lembretes X dias antes
  - Configurável por tenant
  - Histórico de envios
- **Arquivos:**
  - [`app/Console/Commands/SendVencimentoReminders.php`](app/Console/Commands/SendVencimentoReminders.php)
  - [`app/Console/Commands/SendLembretesHoje.php`](app/Console/Commands/SendLembretesHoje.php)

#### Cobranças Atrasadas
- **Status:** ✅ IMPLEMENTADO
- **Prioridade:** ESSENCIAL
- **Funcionalidades:**
  - Identificação automática de cobranças atrasadas
  - Envio de notificações de cobrança
  - Atualização de status
- **Arquivos:**
  - [`app/Console/Commands/SendCobrancasAtrasadas.php`](app/Console/Commands/SendCobrancasAtrasadas.php)
  - [`app/Console/Commands/SendWhatsAppOverdue.php`](app/Console/Commands/SendWhatsAppOverdue.php)

### 1.3 Modelo de Domínio: Tenant → Account → User

#### Arquitetura de Domínio
- **Status:** ⚠️ PRECISA REFACTORING
- **Prioridade:** ESSENCIAL
- **Implementação:**
  - **Tenant**: Representa a organização/empresa (agência, contador, white-label)
  - **Account**: Representa uma conta dentro do tenant (ex: agência gerenciando múltiplos clientes)
  - **User**: Representa o usuário final que faz login
  - Relação: 1 Tenant → N Accounts → N Users
  - Mesmo que inicialmente seja 1:1, o schema já nasce preparado para escalar
- **Arquivos afetados:**
  - [`app/Models/Tenant.php`](app/Models/Tenant.php)
  - [`app/Models/Account.php`](app/Models/Account.php) - NOVO
  - [`app/Models/User.php`](app/Models/User.php)
  - [`database/migrations/`](database/migrations/)
- **Racional:** Prepara o modelo para agências, contadores e white-label desde o início. Evita refactoring massivo quando o negócio crescer.

### 1.4 Multi-Tenancy Básico

#### Isolamento por Subdomínio
- **Status:** ✅ IMPLEMENTADO
- **Prioridade:** ESSENCIAL
- **Funcionalidades:**
  - Subdomínio por tenant
  - Middleware de identificação de tenant
  - Escopo automático de queries
- **Arquivos:**
  - [`app/Http/Middleware/TenantMiddleware.php`](app/Http/Middleware/TenantMiddleware.php)
  - [`app/Models/Tenant.php`](app/Models/Tenant.php)

#### Configurações por Tenant
- **Status:** ✅ IMPLEMENTADO
- **Prioridade:** ESSENCIAL
- **Funcionalidades:**
  - Configurações de WhatsApp por tenant
  - Configurações de e-mail por tenant
  - Configurações de pagamento por tenant
- **Arquivos:**
  - [`app/Models/TenantSettings.php`](app/Models/TenantSettings.php)

### 1.5 Autenticação e Autorização

#### Autenticação com Laravel Sanctum
- **Status:** ✅ IMPLEMENTADO
- **Prioridade:** ESSENCIAL
- **Funcionalidades:**
  - Login via e-mail/senha
  - Token API para integrações
  - Refresh de tokens
- **Arquivos:**
  - [`app/Http/Controllers/Auth/AuthenticatedSessionController.php`](app/Http/Controllers/Auth/AuthenticatedSessionController.php)
  - [`app/Models/ApiKey.php`](app/Models/ApiKey.php)

#### Roles e Permissões
- **Status:** ⚠️ PARCIALMENTE IMPLEMENTADO
- **Prioridade:** ALTA
- **Funcionalidades:**
  - Role: Admin, User
  - Permissões granulares (ver, criar, editar, deletar)
  - Middleware de autorização
- **Arquivos:**
  - [`app/Models/User.php`](app/Models/User.php)
  - [`app/Http/Middleware/`](app/Http/Middleware/)

### 1.6 Dashboard Básico

#### Dashboard de Cobranças
- **Status:** ✅ IMPLEMENTADO
- **Prioridade:** ESSENCIAL
- **Funcionalidades:**
  - Lista de cobranças
  - Filtros por status, data, valor
  - Ações rápidas (enviar, marcar pago)
- **Arquivos:**
  - [`app/Http/Controllers/ClientDashboardController.php`](app/Http/Controllers/ClientDashboardController.php)

#### Dashboard Admin
- **Status:** ✅ IMPLEMENTADO
- **Prioridade:** ESSENCIAL
- **Funcionalidades:**
  - Visão geral de todos os tenants
  - Métricas de MRR e Churn
  - Gestão de usuários
- **Arquivos:**
  - [`app/Http/Controllers/AdminDashboardController.php`](app/Http/Controllers/AdminDashboardController.php)

---

## 📈 FASE 2: ESCALA

**RACIONAL TÉCNICO:** Apenas após o produto estar estável e entregando valor, investimos em escala. Escalar cedo desperdiça recursos e aumenta complexidade desnecessariamente.

### 2.1 Performance

#### Cache de Queries
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** MÉDIA
- **Implementação:**
  - Cache de queries frequentes (tenants, planos)
  - Cache de dashboard
  - Invalidação automática
- **Arquivos:**
  - [`app/Http/Controllers/`](app/Http/Controllers/)
  - [`config/cache.php`](config/cache.php)
- **Racional:** Reduz carga no banco, melhora tempo de resposta

#### Otimização de Banco de Dados
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** MÉDIA
- **Implementação:**
  - Índices em colunas frequentemente consultadas
  - Análise de queries lentas
  - Otimização de N+1 queries
- **Arquivos:**
  - [`database/migrations/`](database/migrations/)
- **Racional:** Melhora performance e reduz custo de infraestrutura

#### CDN para Assets
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** BAIXA
- **Implementação:**
  - CDN para CSS, JS, imagens
  - Cache de assets estáticos
  - Lazy loading de imagens
- **Arquivos:**
  - [`public/`](public/)
- **Racional:** Melhora tempo de carregamento, reduz latência

### 2.2 Escala Horizontal (Ordem ajustada por tráfego real)

#### Filas Distribuídas
- **Status:** ⚠️ PARCIALMENTE IMPLEMENTADO
- **Prioridade:** MÉDIA
- **Implementação:**
  - Redis como broker de filas
  - Múltiplos workers
  - Monitoramento de filas
  - Retry com backoff exponencial
- **Arquivos:**
  - [`config/queue.php`](config/queue.php)
  - [`docker-compose.yml`](docker-compose.yml)
- **Racional:** Processamento assíncrono distribuído, melhora throughput. Essencial antes de escalar horizontalmente.

#### Cache Distribuído
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** MÉDIA
- **Implementação:**
  - Redis cluster
  - Cache compartilhado entre instâncias
  - Session storage em Redis
  - Cache de queries frequentes
- **Arquivos:**
  - [`config/cache.php`](config/cache.php)
  - [`config/session.php`](config/session.php)
- **Racional:** Cache compartilhado, sessões persistentes entre instâncias. Reduz carga no banco.

#### Load Balancing
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** MÉDIA
- **Implementação:**
  - Nginx como load balancer
  - Múltiplas instâncias da aplicação
  - Health checks entre instâncias
  - Sticky sessions se necessário
- **Arquivos:**
  - [`nginx.conf`](nginx.conf)
- **Racional:** Permite escalar horizontalmente, aumenta disponibilidade. Implementar apenas após filas e cache estarem funcionando.

### 2.3 Monitoramento Avançado

#### Métricas de Performance
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** MÉDIA
- **Implementação:**
  - Integração com Prometheus/Grafana
  - Métricas de response time, throughput
  - Métricas de banco de dados, filas
  - Dashboards em tempo real
- **Arquivos:**
  - [`app/Http/Controllers/MetricsController.php`](app/Http/Controllers/MetricsController.php)
- **Racional:** Monitoramento proativo de performance, identificação de bottlenecks. Implementar após ter tráfego real.

#### APM (Application Performance Monitoring)
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** MÉDIA
- **Implementação:**
  - Integração com New Relic, Datadog ou similar
  - Tracing de requests distribuídos
  - Profile de performance
  - Alertas de degradação
- **Arquivos:**
  - [`composer.json`](composer.json)
- **Racional:** Visibilidade profunda da performance, identificação de problemas complexos. Implementar após métricas básicas estarem em uso.

### 2.4 Segurança Avançada (Ordem ajustada por tráfego real)

#### Auditoria de Segurança
- **Status:** ⚠️ PARCIALMENTE IMPLEMENTADO
- **Prioridade:** MÉDIA
- **Implementação:**
  - Logs de auditoria completos
  - Alertas de atividades suspeitas
  - Relatórios de compliance
  - Análise de padrões de acesso
- **Arquivos:**
  - [`app/Models/AuditLog.php`](app/Models/AuditLog.php)
- **Racional:** Compliance, forense em caso de incidentes. Implementar assim que tiver dados reais para auditar.

#### WAF (Web Application Firewall)
- **Status:** ⚠️ PARCIALMENTE IMPLEMENTADO (Cloudflare)
- **Prioridade:** BAIXA
- **Implementação:**
  - Regras WAF customizadas
  - Proteção contra SQL injection, XSS
  - Rate limiting avançado
- **Arquivos:**
  - [`config/cloudflare.php`](config/cloudflare.php)
- **Racional:** Proteção avançada contra ataques web. Implementar apenas após ter tráfego real significativo, caso contrário vira custo e ruído.

#### DDoS Protection
- **Status:** ⚠️ PARCIALMENTE IMPLEMENTADO (Cloudflare)
- **Prioridade:** BAIXA
- **Implementação:**
  - Proteção contra ataques DDoS
  - Rate limiting por IP
  - Challenge de bots
- **Arquivos:**
  - [`config/cloudflare.php`](config/cloudflare.php)
- **Racional:** Proteção contra ataques de negação de serviço. Implementar apenas após ter tráfego real significativo.

---

## 🚀 FASE 3: MARKETING E CRESCIMENTO

**RACIONAL TÉCNICO:** Marketing e features de crescimento só fazem sentido quando o produto é estável, seguro e escalável. Marketing de um produto instável gera churn e reputação negativa.

### 3.1 SEO e Descoberta

#### SEO Básico
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** BAIXA
- **Implementação:**
  - Meta tags otimizadas
  - Sitemap.xml
  - Robots.txt
  - Open Graph tags
- **Arquivos:**
  - [`resources/views/layouts/`](resources/views/layouts/)
- **Racional:** Melhora ranking no Google, aumenta tráfego orgânico

#### Landing Page Otimizada
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** BAIXA
- **Implementação:**
  - Copywriting persuasivo
  - Testimonials
  - CTA claro
  - A/B testing
- **Arquivos:**
  - [`resources/views/landing.blade.php`](resources/views/landing.blade.php)
- **Racional:** Converte visitantes em leads, aumenta conversão

### 3.2 Analytics

#### Google Analytics
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** BAIXA
- **Implementação:**
  - Integração com GA4
  - Event tracking
  - Funnels
- **Arquivos:**
  - [`resources/views/layouts/`](resources/views/layouts/)
- **Racional:** Entendimento do comportamento do usuário, otimização de conversão

#### Heatmaps e Session Recording
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** BAIXA
- **Implementação:**
  - Integração com Hotjar ou similar
  - Heatmaps de cliques
  - Gravação de sessões
- **Arquivos:**
  - [`resources/views/layouts/`](resources/views/layouts/)
- **Racional:** Identificação de problemas de UX, otimização de interface

### 3.3 Marketing Automation

#### E-mail Marketing
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** BAIXA
- **Implementação:**
  - Sequências de onboarding
  - Newsletters
  - Drip campaigns
- **Arquivos:**
  - [`app/Mail/`](app/Mail/)
- **Racional:** Engajamento de leads, retenção de clientes

#### In-App Messaging
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** BAIXA
- **Implementação:**
  - Notificações in-app
  - Announcements
  - Feature highlights
- **Arquivos:**
  - [`resources/views/components/`](resources/views/components/)
- **Racional:** Comunicação direta com usuários, aumento de feature adoption

### 3.4 Onboarding e Time to Value

#### Onboarding Guiado In-App
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** MÉDIA
- **Implementação:**
  - Tour guiado do produto
  - Setup assistido de integrações
  - Checklists de progresso
  - Dicas contextuais
- **Arquivos:**
  - [`resources/views/onboarding/`](resources/views/onboarding/)
  - [`app/Http/Controllers/OnboardingController.php`](app/Http/Controllers/OnboardingController.php)
- **Racional:** Aumenta conversão significativamente mais do que qualquer referral program. Usuários que completam onboarding têm retenção muito maior.

#### Time to First Cobrança < 5 Min
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** MÉDIA
- **Implementação:**
  - Fluxo simplificado de criação de cobrança
  - Templates pré-configurados
  - Integração one-click com WhatsApp
  - Métrica de tracking: tempo até primeira cobrança
- **Arquivos:**
  - [`app/Http/Controllers/CobrancaController.php`](app/Http/Controllers/CobrancaController.php)
  - [`resources/views/cobrancas/`](resources/views/cobrancas/)
- **Racional:** Usuários que enviam a primeira cobrança rapidamente têm muito mais chance de continuar usando. É a métrica mais importante de product-market fit.

### 3.5 Referral Program

#### Sistema de Indicações
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** BAIXA
- **Implementação:**
  - Links de referência únicos
  - Recompensas por indicação
  - Dashboard de indicações
  - Apenas após onboarding e time to value estarem otimizados
- **Arquivos:**
  - [`app/Models/Referral.php`](app/Models/Referral.php)
- **Racional:** Crescimento orgânico via word-of-mouth, CAC reduzido. Implementar apenas após ter retenção estável e onboarding otimizado.

### 3.6 Social Proof

#### Testimonials
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** BAIXA
- **Implementação:**
  - Coleta de testimonials
  - Exibição em landing page
  - Integração com reviews externos
- **Arquivos:**
  - [`app/Models/Testimonial.php`](app/Models/Testimonial.php)
- **Racional:** Prova social, aumenta confiança e conversão

#### Case Studies
- **Status:** ❌ NÃO IMPLEMENTADO
- **Prioridade:** BAIXA
- **Implementação:**
  - Estudos de caso de sucesso
  - Métricas de impacto
  - Histórias de clientes
- **Arquivos:**
  - [`app/Models/CaseStudy.php`](app/Models/CaseStudy.php)
- **Racional:** Demonstração de valor, fechamento de deals enterprise

---

## 📊 RESUMO VISUAL

```
┌─────────────────────────────────────────────────────────────┐
│                    FASE 0: PRÉ-PRODUÇÃO                      │
│  🔒 Segurança Mínima (Rate IP/Tenant/Endpoint)              │
│  👁️ Observabilidade (Logs + Correlation ID + Sentry)       │
│  ⚠️ BLOQUEADOR: Impede qualquer deploy em produção          │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                  FASE 1: SOBREVIVÊNCIA                      │
│  💰 Core de Cobranças + 🤖 Automações                      │
│  🏗️ Modelo de Domínio (Tenant → Account → User)             │
│  ✅ MVP VIÁVEL PARA PRIMEIROS CLIENTES                      │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                      FASE 2: ESCALA                          │
│  🔀 Filas Distribuídas + Cache (primeiro)                   │
│  ⚡ Load Balancing + Métricas + APM (após tráfego real)     │
│  🛡️ WAF + DDoS (apenas após tráfego significativo)         │
│  📈 SUPORTA CRESCIMENTO DE USUÁRIOS                          │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                 FASE 3: MARKETING & CRESCIMENTO              │
│  🚀 Onboarding + Time to Value < 5min (primeiro)            │
│  🔍 SEO + 📈 Analytics + 📧 Marketing Automation            │
│  🎯 Referral Program (após retenção estável)               │
│  💬 Social Proof (consequência, não promessa)                │
└─────────────────────────────────────────────────────────────┘
```

---

## ⚠️ REGRAS DE OURO

1. **NUNCA** pular a Fase 0. Sem segurança e observabilidade, você está voando às cegas.

2. **NÃO** implementar features da Fase 2 ou 3 antes da Fase 1 estar completa. Escalar um produto quebrado é desperdício de recursos.

3. **SEMPRE** validar cada fase antes de avançar. Use métricas e feedback real.

4. **DOCUMENTE** tudo. Sem documentação, você perde conhecimento e torna manutenção difícil.

5. **MONITORE** continuamente. Observabilidade não é "set and forget".

---

## 📝 CHECKLIST DE TRANSIÇÃO ENTRE FASES

### Fase 0 → Fase 1
- [ ] Rate limiting por IP, tenant e endpoint ativo e testado
- [ ] Verificação de e-mail obrigatória implementada
- [ ] Logs estruturados com correlation ID configurados
- [ ] Error tracking (Sentry) integrado
- [ ] Healthcheck endpoint funcional
- [ ] Testes de segurança passando
- [ ] Monitoramento de uptime ativo

### Fase 1 → Fase 2
- [ ] Modelo de domínio Tenant → Account → User implementado
- [ ] Core de cobranças estável
- [ ] Automações funcionando sem erros
- [ ] Multi-tenancy testado
- [ ] Autenticação e autorização robustas
- [ ] Dashboard funcional
- [ ] Primeiros clientes usando sem problemas
- [ ] Feedback positivo de usuários
- [ ] Tráfego real começando a crescer

### Fase 2 → Fase 3
- [ ] Filas distribuídas e cache implementados
- [ ] Performance aceitável (< 500ms p95)
- [ ] Sistema escalável horizontalmente
- [ ] Monitoramento avançado (métricas + APM) ativo
- [ ] Auditoria de segurança implementada
- [ ] WAF e DDoS apenas se tráfego justificar
- [ ] Base de usuários crescente
- [ ] Retenção estável
- [ ] Unit economics positivos

### Fase 3 → Marketing Agressivo
- [ ] Onboarding guiado implementado
- [ ] Time to first cobrança < 5 min
- [ ] Retenção de usuários que completaram onboarding > 70%
- [ ] Produto estável e com feedback positivo
- [ ] Referral program apenas após onboarding otimizado

---

**ÚLTIMA ATUALIZAÇÃO:** 2026-02-04
**VERSÃO:** 2.0 (com correções técnicas)
**STATUS:** FASE 0 EM PROGRESSO
**REVISÃO POR:** Engenheiro Sênior (aprovado com ajustes)
