#!/bin/bash

################################################################################
# 🔧 EXECUTAR MELHORIAS DE SEGURANÇA NA VPS (REMOTO)
################################################################################

set -e

echo "=========================================="
echo "🔧 EXECUTANDO MELHORIAS DE SEGURANÇA NA VPS"
echo "=========================================="
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Configurações
VPS_IP="76.13.167.54"
VPS_USER="root"
SCRIPT_LOCAL="scripts/melhorias-seguranca-seguras.sh"
SCRIPT_REMOTE="/tmp/melhorias-seguranca-seguras.sh"

################################################################################
# ETAPA 1: VERIFICAR CONEXÃO SSH
################################################################################

echo -e "${YELLOW}[1/3] VERIFICANDO CONEXÃO SSH${NC}"
echo "----------------------------------------"

if ! ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "echo 'Conexão OK'"; then
    echo -e "${RED}❌ Não foi possível conectar à VPS${NC}"
    echo "Verifique:"
    echo "1. IP da VPS: ${VPS_IP}"
    echo "2. Usuário: ${VPS_USER}"
    echo "3. Chave SSH configurada"
    echo "4. VPS acessível"
    exit 1
fi

echo -e "${GREEN}✅ Conexão SSH OK${NC}"
echo ""

################################################################################
# ETAPA 2: ENVIAR SCRIPT PARA VPS
################################################################################

echo -e "${YELLOW}[2/3] ENVIANDO SCRIPT PARA VPS${NC}"
echo "----------------------------------------"

if [ ! -f "${SCRIPT_LOCAL}" ]; then
    echo -e "${RED}❌ Script não encontrado: ${SCRIPT_LOCAL}${NC}"
    exit 1
fi

echo "Enviando ${SCRIPT_LOCAL} para ${VPS_IP}..."
scp -o StrictHostKeyChecking=no "${SCRIPT_LOCAL}" ${VPS_USER}@${VPS_IP}:${SCRIPT_REMOTE}
check_command "Envio do script"

echo -e "${GREEN}✅ Script enviado${NC}"
echo ""

################################################################################
# ETAPA 3: EXECUTAR SCRIPT NA VPS
################################################################################

echo -e "${YELLOW}[3/3] EXECUTANDO SCRIPT NA VPS${NC}"
echo "----------------------------------------"

echo "Executando melhorias de segurança..."
ssh -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "chmod +x ${SCRIPT_REMOTE} && ${SCRIPT_REMOTE}"

echo ""
echo "=========================================="
echo -e "${GREEN}✅ MELHORIAS DE SEGURANÇA CONCLUÍDAS!${NC}"
echo "=========================================="
echo ""
echo "📋 PRÓXIMOS PASSOS:"
echo ""
echo "1. ✅ Acesse: https://api.cobrancaauto.com.br"
echo "2. ✅ Verifique se HTTPS está funcionando (cadeado verde)"
echo "3. ✅ Faça login com: admin@cobranca.com / 123456"
echo "4. ✅ Teste o sistema de cobrança"
echo "5. ✅ Verifique logs: tail -f /var/log/nginx/error.log"
echo ""
echo "📋 MONITORAMENTO:"
echo ""
echo "# Verificar status NGINX"
echo "ssh root@${VPS_IP} 'systemctl status nginx'"
echo ""
echo "# Verificar logs NGINX"
echo "ssh root@${VPS_IP} 'tail -f /var/log/nginx/error.log'"
echo ""
echo "# Verificar backups"
echo "ssh root@${VPS_IP} 'ls -lh /backup/'"
echo ""
echo "# Verificar crontab"
echo "ssh root@${VPS_IP} 'crontab -l'"
echo ""
echo "# Testar HTTPS"
echo "curl -I https://api.cobrancaauto.com.br"
echo ""
echo "=========================================="
echo -e "${GREEN}💚 SEGURANÇA REFORÇADA!${NC}"
echo "=========================================="
