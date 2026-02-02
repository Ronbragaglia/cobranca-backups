# Configuração de Produção - DNS Cloudflare

## 1. Labels Traefik para Serviço Nginx/Laravel

### docker-compose.prod.yml - Labels Corretas

```yaml
  nginx-laravel:
    image: nginx:alpine
    container_name: nginx-laravel
    restart: unless-stopped
    volumes:
      - ./:/var/www
      - ./nginx-laravel.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - cobranca_network
      - traefik-network
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.cobranca.rule=Host(`cobrancaauto.com.br`) || Host(`www.cobrancaauto.com.br`) || Host(`app.cobrancaauto.com.br`) || Host(`api.cobrancaauto.com.br`)"
      - "traefik.http.routers.cobranca.entrypoints=websecure"
      - "traefik.http.routers.cobranca.tls.certresolver=cloudflare"
      - "traefik.http.routers.cobranca.tls=true"
      - "traefik.http.services.cobranca.loadbalancer.server.port=80"
```

### SCRIPT_VPS_ETAPA2_APP.sh - Labels Corretas

```yaml
  # Nginx
  nginx:
    image: nginx:alpine
    container_name: cobranca-nginx
    restart: unless-stopped
    ports:
      - "8081:80"
    volumes:
      - ./html:/var/www
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php
    networks:
      - app_network
      - traefik_default
    labels:
      - "traefik.enable=true"
      - "traefik.docker.network=traefik_default"
      - "traefik.http.routers.cobranca.rule=Host(`cobrancaauto.com.br`,`www.cobrancaauto.com.br`,`app.cobrancaauto.com.br`,`api.cobrancaauto.com.br`)"
      - "traefik.http.routers.cobranca.entrypoints=websecure"
      - "traefik.http.routers.cobranca.tls=true"
      - "traefik.http.routers.cobranca.tls.certresolver=cloudflare"
      - "traefik.http.services.cobranca.loadbalancer.server.port=80"
```

## 2. Configuração .env para Produção

### Bloco .env - Configurações Essenciais

```bash
# ============================================
# CONFIGURAÇÃO DE PRODUÇÃO - CLOUDFLARE DNS
# ============================================

APP_NAME="Cobranca API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cobrancaauto.com.br

# Se você tiver app separada, use:
# APP_URL=https://app.cobrancaauto.com.br

# TRUSTED PROXIES - IPs da Cloudflare
# Lista completa de IPs da Cloudflare (IPv4 e IPv6)
TRUSTED_PROXIES=103.21.244.0/22,103.22.200.0/22,103.31.4.0/22,104.16.0.0/13,104.24.0.0/14,108.162.192.0/18,131.0.72.0/22,141.101.64.0/18,162.158.0.0/15,172.64.0.0/13,173.245.48.0/20,188.114.96.0/20,190.93.240.0/20,197.234.240.0/22,198.41.128.0/17,2400:cb00::/32,2606:4700::/32,2803:f800::/32,2405:b500::/32,2405:8100::/32,2c0f:f248::/32,2a06:98c0::/29

# ============================================
# DATABASE
# ============================================
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cobranca
DB_USERNAME=cobranca_user
DB_PASSWORD=__SET_IN_SERVER_ONLY__

# ============================================
# CACHE & QUEUE
# ============================================
BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# ============================================
# REDIS
# ============================================
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# ============================================
# MAIL
# ============================================
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# ============================================
# STRIPE (Configurar em produção)
# ============================================
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

# ============================================
# EVOLUTION API
# ============================================
EVOLUTION_API_URL=http://evolution-api:8080
EVOLUTION_API_USERNAME=admin
EVOLUTION_API_PASSWORD=admin123

# ============================================
# SEGURANÇA ADICIONAL
# ============================================
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
```

## 3. Checklist de Deploy na VPS (10 Passos)

### Passo 1: Acessar VPS via SSH
```bash
ssh root@76.13.167.54
```

### Passo 2: Navegar para diretório do projeto
```bash
cd /opt/app
```

### Passo 3: Atualizar repositório (se usando git)
```bash
# Se já existe repositório:
git pull origin main

# OU clonar pela primeira vez:
git clone https://seu-repositorio.git .
```

### Passo 4: Copiar arquivo .env de produção
```bash
cp .env.production .env
```

### Passo 5: Gerar APP_KEY (se necessário)
```bash
docker compose exec app php artisan key:generate
```

### Passo 6: Buildar containers
```bash
docker compose -f docker-compose.prod.yml build
```

### Passo 7: Iniciar containers em modo detached
```bash
docker compose -f docker-compose.prod.yml up -d
```

### Passo 8: Executar migrations e comandos de setup
```bash
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan storage:link
docker compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker compose -f docker-compose.prod.yml exec app php artisan config:clear
docker compose -f docker-compose.prod.yml exec app php artisan route:clear
docker compose -f docker-compose.prod.yml exec app php artisan view:clear
```

### Passo 9: Verificar logs do Traefik para certificados SSL
```bash
docker logs traefik 2>&1 | grep -i "certificate\|tls\|cobrancaauto"
```

### Passo 10: Testar acesso em cada subdomínio
```bash
# Testar domínio principal
curl -I https://cobrancaauto.com.br

# Testar www
curl -I https://www.cobrancaauto.com.br

# Testar app
curl -I https://app.cobrancaauto.com.br

# Testar API
curl -I https://api.cobrancaauto.com.br

# Verificar status SSL
curl -vI https://cobrancaauto.com.br 2>&1 | grep -i "ssl\|certificate"
```

## 4. Verificações Adicionais

### Verificar containers rodando
```bash
docker ps -a
```

### Verificar logs do app Laravel
```bash
docker logs cobranca_app
docker logs nginx-laravel
```

### Verificar rede Traefik
```bash
docker network ls | grep traefik
docker network inspect traefik-network
```

### Testar conectividade entre containers
```bash
docker compose exec app ping mysql
docker compose exec app ping redis
```

## 5. Troubleshooting Comum

### Certificados SSL não geram
```bash
# Verificar logs do Traefik
docker logs traefik -f

# Reiniciar Traefik
docker restart traefik

# Verificar configuração DNS no Cloudflare
# - Todos os registros A devem apontar para 76.13.167.54
# - Status deve ser "Proxied" (nuvem laranja)
# - CNAME www → cobrancaauto.com.br (Proxied)
```

### Erro 502 Bad Gateway
```bash
# Verificar se o app Laravel está rodando
docker ps | grep cobranca_app

# Verificar logs do app
docker logs cobranca_app

# Reiniciar containers
docker compose -f docker-compose.prod.yml restart
```

### Erro de permissões
```bash
# Corrigir permissões do storage
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

## 6. URLs de Produção

| Subdomínio | URL | Propósito |
|------------|-----|-----------|
| Raiz | https://cobrancaauto.com.br | Aplicação principal |
| www | https://www.cobrancaauto.com.br | Redireciona para raiz |
| app | https://app.cobrancaauto.com.br | Dashboard do usuário |
| api | https://api.cobrancaauto.com.br | API REST |

## 7. Monitoramento

### Verificar logs em tempo real
```bash
# Todos os containers
docker compose -f docker-compose.prod.yml logs -f

# Apenas app
docker compose -f docker-compose.prod.yml logs -f app

# Apenas nginx
docker compose -f docker-compose.prod.yml logs -f nginx-laravel
```

### Verificar uso de recursos
```bash
docker stats
```

## 8. Backup Automático

O container `backup` executa backup diário automaticamente. Para verificar:
```bash
docker logs cobranca_backup
```

Backups são salvos no volume `backups` em `/backups/`.
