#!/bin/bash

################################################################################
# 🔧 DIAGNOSTICAR CONFIGURAÇÃO NGINX COMPLETA
################################################################################

set -e

echo "=========================================="
echo "🔧 DIAGNOSTICANDO CONFIGURAÇÃO NGINX COMPLETA"
echo "=========================================="
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

################################################################################
# ETAPA 1: VERIFICAR ARQUIVO DE CONFIGURAÇÃO
################################################################################

echo -e "${YELLOW}[1/5] VERIFICANDO ARQUIVO DE CONFIGURAÇÃO${NC}"
echo "----------------------------------------"

echo "Verificando arquivo /etc/nginx/sites-available/cobranca-api..."
cat /etc/nginx/sites-available/cobranca-api
echo ""
echo -e "${GREEN}✅ Arquivo exibido${NC}"
echo ""

################################################################################
# ETAPA 2: VERIFICAR SYMLINK
################################################################################

echo -e "${YELLOW}[2/5] VERIFICANDO SYMLINK${NC}"
echo "----------------------------------------"

echo "Verificando symlink..."
ls -la /etc/nginx/sites-enabled/cobranca-api
echo ""
echo -e "${GREEN}✅ Symlink verificado${NC}"
echo ""

################################################################################
# ETAPA 3: VERIFICAR LINHAS DE LIMIT_REQ
################################################################################

echo -e "${YELLOW}[3/5] VERIFICANDO LINHAS DE LIMIT_REQ${NC}"
echo "----------------------------------------"

echo "Contando linhas de limit_req_zone..."
grep -n "limit_req_zone" /etc/nginx/sites-available/cobranca-api
echo ""
echo "Contando linhas de limit_req..."
grep -n "limit_req" /etc/nginx/sites-available/cobranca-api
echo ""
echo -e "${GREEN}✅ Linhas contadas${NC}"
echo ""

################################################################################
# ETAPA 4: CRIAR CONFIGURAÇÃO LIMPA
################################################################################

echo -e "${YELLOW}[4/5] CRIANDO CONFIGURAÇÃO LIMPA${NC}"
echo "----------------------------------------"

# Backup da configuração atual
cp /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-available/cobranca-api.backup.$(date +%Y%m%d_%H%M%S)

# Criar configuração limpa
cat > /etc/nginx/sites-available/cobranca-api << 'NGINXEOF'
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;

server {
    listen 80;
    listen [::]:80;
    server_name api.cobrancaauto.com.br;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.cobrancaauto.com.br;
    
    root /var/www/cobranca-api/public;
    index index.php index.html index.htm;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/api.cobrancaauto.com.br/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.cobrancaauto.com.br/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Logging
    access_log /var/log/nginx/cobranca-api-access.log;
    error_log /var/log/nginx/cobranca-api-error.log;
    
    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
        limit_req zone=api burst=20 nodelay;
    }
    
    # PHP-FPM
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
    }
    
    # Deny access to hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
}
NGINXEOF

echo -e "${GREEN}✅ Configuração limpa criada${NC}"
echo ""

################################################################################
# ETAPA 5: TESTAR E RECARREGAR NGINX
################################################################################

echo -e "${YELLOW}[5/5] TESTANDO E RECARREGANDO NGINX${NC}"
echo "----------------------------------------"

# Testar configuração
echo "Testando configuração NGINX..."
nginx -t
check_command "Teste NGINX"

# Recarregar NGINX
echo "Recarregando NGINX..."
systemctl reload nginx
check_command "Recarregar NGINX"

echo -e "${GREEN}✅ NGINX recarregado${NC}"
echo ""

################################################################################
# VERIFICAÇÃO FINAL
################################################################################

echo "=========================================="
echo -e "${GREEN}✅ CONFIGURAÇÃO NGINX CORRIGIDA!${NC}"
echo "=========================================="
echo ""
echo "📋 VERIFICAÇÕES:"
echo ""
echo "# Verificar configuração NGINX"
echo "nginx -t"
echo ""
echo "# Verificar limit_req_zone"
echo "grep 'limit_req' /etc/nginx/sites-available/cobranca-api"
echo ""
echo "# Verificar status NGINX"
echo "systemctl status nginx"
echo ""
echo "# Testar HTTPS"
echo "curl -I https://api.cobrancaauto.com.br"
echo ""
echo "# Testar HTTP (deve redirecionar para HTTPS)"
echo "curl -I http://api.cobrancaauto.com.br"
echo ""
echo "=========================================="
echo -e "${GREEN}💚 NGINX CONFIGURADO CORRETAMENTE!${NC}"
echo "=========================================="
