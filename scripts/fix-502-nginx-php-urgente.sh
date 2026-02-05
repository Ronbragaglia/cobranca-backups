#!/bin/bash

################################################################################
# 🔥 FIX URGENTE 502 BAD GATEWAY - NGINX + PHP-FPM
# Script de diagnóstico e solução automática
# Execute como ROOT na VPS: ssh root@76.13.167.54
################################################################################

set -e

echo "=========================================="
echo "🚨 FIX URGENTE 502 BAD GATEWAY"
echo "=========================================="
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

################################################################################
# ETAPA 1: DIAGNÓSTICO
################################################################################
echo -e "${YELLOW}[1/7] DIAGNÓSTICO INICIAL${NC}"
echo "----------------------------------------"

# Verificar PHP-FPM rodando
echo "📌 PHP-FPM Status:"
systemctl status php8.2-fpm --no-pager || echo "PHP-FPM não está rodando!"
echo ""

# Verificar NGINX rodando
echo "📌 NGINX Status:"
systemctl status nginx --no-pager || echo "NGINX não está rodando!"
echo ""

# Verificar socket PHP-FPM
echo "📌 Socket PHP-FPM:"
ls -la /var/run/php/php8.2-fpm.sock 2>/dev/null || echo "SOCKET NÃO EXISTE!"
echo ""

# Verificar versão PHP
echo "📌 Versão PHP:"
php -v
echo ""

################################################################################
# ETAPA 2: VERIFICAR CONFIGURAÇÃO NGINX
################################################################################
echo -e "${YELLOW}[2/7] VERIFICANDO CONFIGURAÇÃO NGINX${NC}"
echo "----------------------------------------"

NGINX_CONF="/etc/nginx/sites-available/cobranca-api"
echo "📌 Configuração atual:"
cat $NGINX_CONF
echo ""

# Verificar se a configuração está correta
echo "📌 Testando configuração NGINX:"
nginx -t || echo "ERRO NA CONFIGURAÇÃO NGINX!"
echo ""

################################################################################
# ETAPA 3: VERIFICAR LOGS DE ERRO
################################################################################
echo -e "${YELLOW}[3/7] VERIFICANDO LOGS DE ERRO${NC}"
echo "----------------------------------------"

echo "📌 Últimas 20 linhas do NGINX error.log:"
tail -20 /var/log/nginx/error.log
echo ""

echo "📌 Últimas 20 linhas do PHP-FPM error.log:"
tail -20 /var/log/php8.2-fpm.log 2>/dev/null || echo "Log não encontrado"
echo ""

################################################################################
# ETAPA 4: VERIFICAR PERMISSÕES
################################################################################
echo -e "${YELLOW}[4/7] VERIFICANDO PERMISSÕES${NC}"
echo "----------------------------------------"

echo "📌 Permissões do diretório Laravel:"
ls -la /var/www/cobranca-api/public/ | head -20
echo ""

echo "📌 Permissões do socket PHP-FPM:"
ls -la /var/run/php/ 2>/dev/null || echo "Diretório não encontrado"
echo ""

echo "📌 Usuário do NGINX:"
grep user /etc/nginx/nginx.conf | grep -v '#'
echo ""

echo "📌 Usuário do PHP-FPM:"
grep -E "^(user|group)" /etc/php/8.2/fpm/pool.d/www.conf | grep -v ';'
echo ""

################################################################################
# ETAPA 5: CRIAR CONFIGURAÇÃO CORRETA NGINX
################################################################################
echo -e "${YELLOW}[5/7] CRIANDO CONFIGURAÇÃO CORRETA NGINX${NC}"
echo "----------------------------------------"

# Backup da configuração atual
cp $NGINX_CONF ${NGINX_CONF}.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ Backup criado: ${NGINX_CONF}.backup.$(date +%Y%m%d_%H%M%S)"

# Criar configuração correta
cat > $NGINX_CONF << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name api.cobrancaauto.com.br;

    root /var/www/cobranca-api/public;
    index index.php index.html index.htm;

    # Logs
    access_log /var/log/nginx/cobranca-api-access.log;
    error_log /var/log/nginx/cobranca-api-error.log;

    # Tamanho máximo de upload
    client_max_body_size 100M;

    # Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM - CONFIGURAÇÃO CORRETA
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        
        # Usar socket UNIX (mais rápido)
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        
        # Parâmetros adicionais
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Timeouts
        fastcgi_read_timeout 300;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Negar acesso a arquivos ocultos
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Negar acesso a arquivos sensíveis
    location ~ /\.(?:git|svn|hg|bzr) {
        deny all;
    }
}
EOF

echo "✅ Configuração NGINX atualizada!"
echo ""

################################################################################
# ETAPA 6: CORRIGIR PERMISSÕES E REINICIAR SERVIÇOS
################################################################################
echo -e "${YELLOW}[6/7] CORRIGINDO PERMISSÕES E REINICIANDO SERVIÇOS${NC}"
echo "----------------------------------------"

# Garantir permissões corretas
echo "📌 Corrigindo permissões Laravel:"
cd /var/www/cobranca-api
chown -R www-data:www-data /var/www/cobranca-api
chmod -R 755 /var/www/cobranca-api
chmod -R 775 /var/www/cobranca-api/storage
chmod -R 775 /var/www/cobranca-api/bootstrap/cache
echo "✅ Permissões corrigidas!"
echo ""

# Reiniciar PHP-FPM
echo "📌 Reiniciando PHP-FPM:"
systemctl restart php8.2-fpm
systemctl status php8.2-fpm --no-pager
echo ""

# Reiniciar NGINX
echo "📌 Reiniciando NGINX:"
systemctl restart nginx
systemctl status nginx --no-pager
echo ""

################################################################################
# ETAPA 7: TESTAR SOLUÇÃO
################################################################################
echo -e "${YELLOW}[7/7] TESTANDO SOLUÇÃO${NC}"
echo "----------------------------------------"

# Criar arquivo de teste PHP
echo "📌 Criando arquivo de teste PHP:"
cat > /var/www/cobranca-api/public/test-php.php << 'EOF'
<?php
phpinfo();
EOF
chown www-data:www-data /var/www/cobranca-api/public/test-php.php
echo "✅ Arquivo de teste criado!"
echo ""

# Testar via curl local
echo "📌 Testando via curl local:"
curl -I http://localhost/test-php.php 2>&1 | head -10
echo ""

# Testar via PHP-FPM direto
echo "📌 Testando comunicação NGINX-PHP-FPM:"
SCRIPT_FILENAME=/var/www/cobranca-api/public/test-php.php \
REQUEST_METHOD=GET \
SCRIPT_NAME=/test-php.php \
QUERY_STRING= \
DOCUMENT_ROOT=/var/www/cobranca-api/public \
SERVER_SOFTWARE=nginx \
cgi-fcgi -bind -connect /var/run/php/php8.2-fpm.sock 2>&1 | head -20
echo ""

################################################################################
# RESUMO FINAL
################################################################################
echo "=========================================="
echo -e "${GREEN}✅ DIAGNÓSTICO E SOLUÇÃO CONCLUÍDOS${NC}"
echo "=========================================="
echo ""
echo "📌 VERIFICAÇÕES FINAIS:"
echo ""
echo "1. Teste o site no navegador:"
echo "   http://api.cobrancaauto.com.br"
echo ""
echo "2. Teste o arquivo PHP:"
echo "   http://api.cobrancaauto.com.br/test-php.php"
echo ""
echo "3. Verifique logs se ainda houver erro:"
echo "   tail -f /var/log/nginx/error.log"
echo "   tail -f /var/log/php8.2-fpm.log"
echo ""
echo "4. Se funcionar, remova o arquivo de teste:"
echo "   rm /var/www/cobranca-api/public/test-php.php"
echo ""
echo "📌 SERVIÇOS STATUS:"
systemctl status php8.2-fpm --no-pager | head -5
systemctl status nginx --no-pager | head -5
echo ""
echo "=========================================="
echo "💚 Site deve estar funcionando agora!"
echo "=========================================="
