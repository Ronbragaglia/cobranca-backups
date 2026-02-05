#!/bin/bash

################################################################################
# 🔧 CORRIGIR DUPLICIDADE DO LIMIT_REQ_ZONE NO NGINX
################################################################################

set -e

echo "=========================================="
echo "🔧 CORRIGINDO DUPLICIDADE DO LIMIT_REQ_ZONE"
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

echo -e "${YELLOW}[1/4] CRIANDO BACKUP DA CONFIGURAÇÃO ATUAL${NC}"
echo "----------------------------------------"

cp /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-available/cobranca-api.backup.$(date +%Y%m%d_%H%M%S)
echo -e "${GREEN}✅ Backup criado${NC}"
echo ""

################################################################################
# ETAPA 2: REMOVER TODAS AS LINHAS DE LIMIT_REQ_ZONE
################################################################################

echo -e "${YELLOW}[2/4] REMOVENDO TODAS AS LINHAS DE LIMIT_REQ_ZONE${NC}"
echo "----------------------------------------"

# Remover todas as linhas de limit_req_zone
sed -i '/limit_req_zone/d' /etc/nginx/sites-available/cobranca-api
echo -e "${GREEN}✅ Linhas de limit_req_zone removidas${NC}"
echo ""

################################################################################
# ETAPA 3: ADICIONAR LIMIT_REQ_ZONE NA POSIÇÃO CORRETA
################################################################################

echo -e "${YELLOW}[3/4] ADICIONAR LIMIT_REQ_ZONE NA POSIÇÃO CORRETA${NC}"
echo "----------------------------------------"

# Adicionar limit_req_zone antes do bloco server
sed -i '/^server {/i limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;' /etc/nginx/sites-available/cobranca-api
echo -e "${GREEN}✅ limit_req_zone adicionado na posição correta${NC}"
echo ""

################################################################################
# ETAPA 4: TESTAR E RECARREGAR NGINX
################################################################################

echo -e "${YELLOW}[4/4] TESTANDO E RECARREGANDO NGINX${NC}"
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
echo -e "${GREEN}✅ DUPLICIDADE CORRIGIDA!${NC}"
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
echo "=========================================="
echo -e "${GREEN}💚 NGINX CONFIGURADO CORRETAMENTE!${NC}"
echo "=========================================="
