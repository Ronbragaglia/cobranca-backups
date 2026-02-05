#!/bin/bash

################################################################################
# 🔧 CORRIGIR CONFIGURAÇÃO NGINX (RATE LIMIT ZONE)
################################################################################

set -e

echo "=========================================="
echo "🔧 CORRIGINDO CONFIGURAÇÃO NGINX"
echo "=========================================="
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

################################################################################
# ETAPA 1: BACKUP DA CONFIGURAÇÃO ATUAL
################################################################################

echo -e "${YELLOW}[1/3] CRIANDO BACKUP DA CONFIGURAÇÃO ATUAL${NC}"
echo "----------------------------------------"

cp /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-available/cobranca-api.backup.$(date +%Y%m%d_%H%M%S)
echo -e "${GREEN}✅ Backup criado${NC}"
echo ""

################################################################################
# ETAPA 2: CORRIGIR POSIÇÃO DO RATE LIMIT ZONE
################################################################################

echo -e "${YELLOW}[2/3] CORRIGINDO POSIÇÃO DO RATE LIMIT ZONE${NC}"
echo "----------------------------------------"

# Remover rate limit zone do final do arquivo
sed -i '/^limit_req_zone/d' /etc/nginx/sites-available/cobranca-api

# Adicionar rate limit zone antes do bloco server
sed -i '/^server {/i limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;' /etc/nginx/sites-available/cobranca-api

echo -e "${GREEN}✅ Rate limit zone movido para posição correta${NC}"
echo ""

################################################################################
# ETAPA 3: TESTAR E RECARREGAR NGINX
################################################################################

echo -e "${YELLOW}[3/3] TESTANDO E RECARREGANDO NGINX${NC}"
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
echo "# Verificar rate limit zone"
echo "grep 'limit_req_zone' /etc/nginx/sites-available/cobranca-api"
echo ""
echo "# Verificar rate limit na location"
echo "grep 'limit_req zone=api' /etc/nginx/sites-available/cobranca-api"
echo ""
echo "# Verificar status NGINX"
echo "systemctl status nginx"
echo ""
echo "# Verificar logs NGINX"
echo "tail -f /var/log/nginx/error.log"
echo ""
echo "=========================================="
echo -e "${GREEN}💚 NGINX CONFIGURADO CORRETAMENTE!${NC}"
echo "=========================================="
