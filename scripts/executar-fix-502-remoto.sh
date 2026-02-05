#!/bin/bash

################################################################################
# 🔥 EXECUTAR FIX 502 BAD GATEWAY - REMOTO
# Este script conecta na VPS e executa a solução automaticamente
################################################################################

set -e

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Configuração VPS
VPS_HOST="76.13.167.54"
VPS_USER="root"
VPS_SSH="${VPS_USER}@${VPS_HOST}"

echo "=========================================="
echo "🚨 EXECUTANDO FIX 502 BAD GATEWAY"
echo "=========================================="
echo ""
echo "📌 VPS: ${VPS_HOST}"
echo "📌 Usuário: ${VPS_USER}"
echo ""

# Verificar se o usuário tem acesso SSH
echo -e "${YELLOW}[1/5] Verificando acesso SSH...${NC}"
if ! ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no ${VPS_SSH} "echo 'SSH OK'" > /dev/null 2>&1; then
    echo -e "${RED}❌ Erro: Não foi possível conectar na VPS${NC}"
    echo "Verifique:"
    echo "  - A VPS está online?"
    echo "  - Você tem acesso SSH?"
    echo "  - A chave SSH está configurada?"
    exit 1
fi
echo -e "${GREEN}✅ SSH OK${NC}"
echo ""

# Verificar se o script existe localmente
echo -e "${YELLOW}[2/5] Verificando script local...${NC}"
SCRIPT_LOCAL="scripts/fix-502-nginx-php-urgente.sh"
if [ ! -f "$SCRIPT_LOCAL" ]; then
    echo -e "${RED}❌ Erro: Script não encontrado: $SCRIPT_LOCAL${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Script local encontrado${NC}"
echo ""

# Enviar script para VPS
echo -e "${YELLOW}[3/5] Enviando script para VPS...${NC}"
scp -o StrictHostKeyChecking=no "$SCRIPT_LOCAL" "${VPS_SSH}:/root/fix-502-nginx-php-urgente.sh"
echo -e "${GREEN}✅ Script enviado${NC}"
echo ""

# Executar script na VPS
echo -e "${YELLOW}[4/5] Executando script na VPS...${NC}"
echo "----------------------------------------"
ssh -o StrictHostKeyChecking=no ${VPS_SSH} "chmod +x /root/fix-502-nginx-php-urgente.sh && /root/fix-502-nginx-php-urgente.sh"
echo "----------------------------------------"
echo ""

# Testar site
echo -e "${YELLOW}[5/5] Testando site...${NC}"
echo "📌 Testando via curl na VPS:"
ssh -o StrictHostKeyChecking=no ${VPS_SSH} "curl -I http://localhost/ 2>&1 | head -10"
echo ""

# Resumo
echo "=========================================="
echo -e "${GREEN}✅ FIX 502 CONCLUÍDO!${NC}"
echo "=========================================="
echo ""
echo "📌 VERIFICAÇÕES FINAIS:"
echo ""
echo "1. Teste o site no navegador:"
echo "   http://api.cobrancaauto.com.br"
echo ""
echo "2. Teste a API:"
echo "   http://api.cobrancaauto.com.br/api/status"
echo ""
echo "3. Se funcionar, remova o arquivo de teste:"
echo "   ssh ${VPS_SSH} 'rm /var/www/cobranca-api/public/test-php.php'"
echo ""
echo "=========================================="
echo -e "${GREEN}💚 Site deve estar funcionando agora!${NC}"
echo "=========================================="
