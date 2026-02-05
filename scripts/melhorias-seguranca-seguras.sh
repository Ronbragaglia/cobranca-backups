#!/bin/bash

################################################################################
# 🔧 MELHORIAS DE SEGURANÇA (SEM QUEBRAR O SITE)
################################################################################

set -e

echo "=========================================="
echo "🔧 MELHORIAS DE SEGURANÇA SEGUROS"
echo "=========================================="
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Função de verificação
check_command() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ $1${NC}"
    else
        echo -e "${RED}❌ $1${NC}"
        exit 1
    fi
}

################################################################################
# ETAPA 1: BACKUP DO SISTEMA (ANTES DE MUDAR QUALQUER COISA)
################################################################################

echo -e "${YELLOW}[1/6] CRIANDO BACKUP DO SISTEMA${NC}"
echo "----------------------------------------"

# Backup NGINX
echo "Backup NGINX..."
cp /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-available/cobranca-api.backup.$(date +%Y%m%d_%H%M%S)
check_command "Backup NGINX"

# Backup banco de dados
echo "Backup banco de dados..."
mysqldump -u root cobranca > /backup/cobranca-pre-melhorias-$(date +%Y%m%d_%H%M%S).sql
check_command "Backup banco de dados"

echo -e "${GREEN}✅ Backup criado${NC}"
echo ""

################################################################################
# ETAPA 2: HTTPS SSL COM CERTBOT
################################################################################

echo -e "${YELLOW}[2/6] CONFIGURANDO HTTPS SSL COM CERTBOT${NC}"
echo "----------------------------------------"

# Verificar se certbot está instalado
if ! command -v certbot &> /dev/null; then
    echo "Instalando certbot..."
    apt update
    apt install certbot python3-certbot-nginx -y
    check_command "Instalação certbot"
else
    echo -e "${GREEN}✅ Certbot já instalado${NC}"
fi

# Configurar SSL
echo "Configurando SSL para api.cobrancaauto.com.br..."
certbot --nginx -d api.cobrancaauto.com.br --non-interactive --agree-tos --email admin@cobrancaauto.com.br
check_command "Configuração SSL"

# Recarregar NGINX
echo "Recarregando NGINX..."
systemctl reload nginx
check_command "Recarregar NGINX"

echo -e "${GREEN}✅ HTTPS SSL configurado${NC}"
echo ""

################################################################################
# ETAPA 3: LIMPAR CONFIGURAÇÃO DUPLICADA NGINX
################################################################################

echo -e "${YELLOW}[3/6] LIMPANDO CONFIGURAÇÃO DUPLICADA NGINX${NC}"
echo "----------------------------------------"

# Remover site default se existir
if [ -f /etc/nginx/sites-enabled/default ]; then
    echo "Removendo site default..."
    rm /etc/nginx/sites-enabled/default
    check_command "Remover site default"
else
    echo -e "${GREEN}✅ Site default já removido${NC}"
fi

# Testar configuração NGINX
echo "Testando configuração NGINX..."
nginx -t
check_command "Teste NGINX"

# Recarregar NGINX
echo "Recarregando NGINX..."
systemctl reload nginx
check_command "Recarregar NGINX"

echo -e "${GREEN}✅ NGINX limpo e configurado${NC}"
echo ""

################################################################################
# ETAPA 4: BACKUP DIÁRIO DO BANCO DE DADOS
################################################################################

echo -e "${YELLOW}[4/6] CONFIGURANDO BACKUP DIÁRIO DO BANCO DE DADOS${NC}"
echo "----------------------------------------"

# Criar diretório de backup
echo "Criando diretório de backup..."
mkdir -p /backup
check_command "Criar diretório /backup"

# Adicionar crontab
echo "Adicionando crontab para backup diário..."
(crontab -l 2>/dev/null | grep -v "mysqldump cobranca"; echo "0 2 * * * mysqldump -u root cobranca > /backup/cobranca-\$(date +\%Y\%m\%d).sql") | crontab -
check_command "Adicionar crontab"

echo -e "${GREEN}✅ Backup diário configurado (2:00 AM)${NC}"
echo ""

################################################################################
# ETAPA 5: RATE LIMITING NO NGINX
################################################################################

echo -e "${YELLOW}[5/6] CONFIGURANDO RATE LIMITING NO NGINX${NC}"
echo "----------------------------------------"

# Backup configuração atual
cp /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-available/cobranca-api.before-rate-limit

# Adicionar rate limit zone
echo "Adicionando rate limit zone..."
if ! grep -q "limit_req_zone" /etc/nginx/sites-available/cobranca-api; then
    # Adicionar antes de server block
    sed -i '/^server {/i limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;' /etc/nginx/sites-available/cobranca-api
    check_command "Adicionar rate limit zone"
else
    echo -e "${GREEN}✅ Rate limit zone já existe${NC}"
fi

# Adicionar rate limit na location /
echo "Adicionando rate limit na location /..."
if ! grep -q "limit_req zone=api" /etc/nginx/sites-available/cobranca-api; then
    # Adicionar dentro de location / {
    sed -i '/location \//,/}/ s/}/\n            limit_req zone=api burst=20 nodelay;\n        }/' /etc/nginx/sites-available/cobranca-api
    check_command "Adicionar rate limit na location"
else
    echo -e "${GREEN}✅ Rate limit já configurado${NC}"
fi

# Testar configuração NGINX
echo "Testando configuração NGINX..."
nginx -t
check_command "Teste NGINX"

# Recarregar NGINX
echo "Recarregando NGINX..."
systemctl reload nginx
check_command "Recarregar NGINX"

echo -e "${GREEN}✅ Rate limiting configurado (10 req/s, burst 20)${NC}"
echo ""

################################################################################
# ETAPA 6: VERIFICAÇÃO FINAL
################################################################################

echo -e "${YELLOW}[6/6] VERIFICAÇÃO FINAL${NC}"
echo "----------------------------------------"

# Verificar HTTPS
echo "Verificando HTTPS..."
curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" https://api.cobrancaauto.com.br
check_command "HTTPS funcionando"

# Verificar NGINX
echo "Verificando NGINX..."
systemctl status nginx | grep "active (running)"
check_command "NGINX rodando"

# Verificar PHP-FPM
echo "Verificando PHP-FPM..."
systemctl status php8.2-fpm | grep "active (running)"
check_command "PHP-FPM rodando"

# Verificar crontab
echo "Verificando crontab..."
crontab -l | grep "mysqldump cobranca"
check_command "Crontab configurado"

# Verificar rate limit
echo "Verificando rate limit..."
grep "limit_req_zone" /etc/nginx/sites-available/cobranca-api
check_command "Rate limit configurado"

echo ""
echo "=========================================="
echo -e "${GREEN}✅ MELHORIAS DE SEGURANÇA IMPLEMENTADAS!${NC}"
echo "=========================================="
echo ""
echo "📋 MELHORIAS IMPLEMENTADAS:"
echo ""
echo "1. ✅ HTTPS SSL configurado com Certbot"
echo "2. ✅ NGINX limpo (removido site default)"
echo "3. ✅ Backup diário do banco de dados (2:00 AM)"
echo "4. ✅ Rate limiting configurado (10 req/s, burst 20)"
echo ""
echo "📋 VERIFICAÇÕES:"
echo ""
echo "✅ HTTPS: https://api.cobrancaauto.com.br"
echo "✅ NGINX: Rodando e configurado"
echo "✅ PHP-FPM: Rodando"
echo "✅ Crontab: Backup diário configurado"
echo "✅ Rate Limit: Proteção contra ataques"
echo ""
echo "📋 BACKUPS CRIADOS:"
echo ""
echo "✅ NGINX: /etc/nginx/sites-available/cobranca-api.backup.*"
echo "✅ Banco: /backup/cobranca-pre-melhorias-*.sql"
echo ""
echo "📋 COMANDOS ÚTEIS:"
echo ""
echo "# Verificar status NGINX"
echo "systemctl status nginx"
echo ""
echo "# Verificar logs NGINX"
echo "tail -f /var/log/nginx/error.log"
echo ""
echo "# Verificar status PHP-FPM"
echo "systemctl status php8.2-fpm"
echo ""
echo "# Verificar logs PHP-FPM"
echo "tail -f /var/log/php8.2-fpm.log"
echo ""
echo "# Verificar backups"
echo "ls -lh /backup/"
echo ""
echo "# Verificar crontab"
echo "crontab -l"
echo ""
echo "# Testar HTTPS"
echo "curl -I https://api.cobrancaauto.com.br"
echo ""
echo "# Testar rate limit (simular ataque)"
echo "for i in {1..30}; do curl https://api.cobrancaauto.com.br; done"
echo ""
echo "=========================================="
echo -e "${GREEN}💚 SEGURANÇA REFORÇADA!${NC}"
echo "=========================================="
