#!/bin/bash

# =============================================================================
# SCRIPT DEPLOY AUTOMÁTICO - LOCAL → VPS HOSTINGER
# VPS: 76.13.167.54 (Ubuntu 22.04, root)
# Senha SSH: 1Qaz2wsx@2026
# =============================================================================

set -e  # Para em caso de erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

VPS_IP="76.13.167.54"
VPS_USER="root"
VPS_PASSWORD="1Qaz2wsx@2026"
VPS_DIR="/var/www/cobranca-auto"
LOCAL_ARCHIVE="cobranca-api.tar.gz"

# Função para executar comando SSH com senha
ssh_exec() {
    sshpass -p "$VPS_PASSWORD" ssh -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "$1"
}

# Função para executar SCP com senha
scp_exec() {
    sshpass -p "$VPS_PASSWORD" scp -o StrictHostKeyChecking=no "$1" ${VPS_USER}@${VPS_IP}:"$2"
}

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}DEPLOY AUTOMÁTICO - VPS HOSTINGER${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# =============================================================================
# COMANDO 1: Testar conexão SSH
# =============================================================================
echo -e "${YELLOW}[1/10] Testando conexão SSH...${NC}"
ssh_exec "echo '✓ Conexão SSH OK' && exit"
echo -e "${GREEN}✓ Comando 1 concluído${NC}"
echo ""

# =============================================================================
# COMANDO 2: Instalar Docker e Docker Compose
# =============================================================================
echo -e "${YELLOW}[2/10] Instalando Docker e Docker Compose...${NC}"
ssh_exec "apt-get update && apt-get install -y docker.io docker-compose && systemctl enable docker && systemctl start docker && docker --version && docker-compose --version"
echo -e "${GREEN}✓ Comando 2 concluído${NC}"
echo ""

# =============================================================================
# COMANDO 3: Criar diretório do projeto
# =============================================================================
echo -e "${YELLOW}[3/10] Criando diretório do projeto na VPS...${NC}"
ssh_exec "mkdir -p ${VPS_DIR} && ls -la /var/www/"
echo -e "${GREEN}✓ Comando 3 concluído${NC}"
echo ""

# =============================================================================
# COMANDO 4: Compactar projeto local
# =============================================================================
echo -e "${YELLOW}[4/10] Compactando projeto local...${NC}"
tar -czf ${LOCAL_ARCHIVE} \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.git' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  .
echo -e "${GREEN}✓ Comando 4 concluído - Arquivo: ${LOCAL_ARCHIVE}${NC}"
echo ""

# =============================================================================
# COMANDO 5: Transferir arquivo para VPS
# =============================================================================
echo -e "${YELLOW}[5/10] Transferindo arquivo para VPS...${NC}"
scp_exec "${LOCAL_ARCHIVE}" "/var/www/${LOCAL_ARCHIVE}"
echo -e "${GREEN}✓ Comando 5 concluído${NC}"
echo ""

# =============================================================================
# COMANDO 6: Extrair projeto na VPS
# =============================================================================
echo -e "${YELLOW}[6/10] Extraindo projeto na VPS...${NC}"
ssh_exec "cd /var/www && tar -xzf ${LOCAL_ARCHIVE} -C ${VPS_DIR} && rm ${LOCAL_ARCHIVE} && ls -la ${VPS_DIR}/"
echo -e "${GREEN}✓ Comando 6 concluído${NC}"
echo ""

# =============================================================================
# COMANDO 7: Configurar firewall
# =============================================================================
echo -e "${YELLOW}[7/10] Configurando firewall...${NC}"
ssh_exec "ufw allow 80/tcp && ufw allow 8080/tcp && ufw allow 22/tcp && ufw --force enable && ufw status"
echo -e "${GREEN}✓ Comando 7 concluído${NC}"
echo ""

# =============================================================================
# COMANDO 8: Rodar docker-compose.dev.yml
# =============================================================================
echo -e "${YELLOW}[8/10] Iniciando containers Docker...${NC}"
echo -e "${YELLOW}⚠️  Este comando pode levar 5-10 minutos (build das imagens)${NC}"
ssh_exec "cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml up -d --build"
echo -e "${GREEN}✓ Comando 8 concluído${NC}"
echo ""

# =============================================================================
# COMANDO 9: Testar aplicação
# =============================================================================
echo -e "${YELLOW}[9/10] Testando aplicação...${NC}"
ssh_exec "sleep 15 && docker ps && curl -I http://localhost:8000 && echo '---' && curl -I http://localhost:8080"
echo -e "${GREEN}✓ Comando 9 concluído${NC}"
echo ""

# =============================================================================
# COMANDO 10: Ver logs e validar
# =============================================================================
echo -e "${YELLOW}[10/10] Verificando logs...${NC}"
ssh_exec "cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml logs --tail=50 app && echo '---' && docker-compose -f docker-compose.dev.yml logs --tail=20 mysql"
echo -e "${GREEN}✓ Comando 10 concluído${NC}"
echo ""

# =============================================================================
# RESUMO FINAL
# =============================================================================
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✓ DEPLOY CONCLUÍDO COM SUCESSO!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Acesse a aplicação:"
echo -e "  • Laravel: ${GREEN}http://${VPS_IP}:8000${NC}"
echo -e "  • phpMyAdmin: ${GREEN}http://${VPS_IP}:8080${NC}"
echo -e "    Usuário: root"
echo -e "    Senha: root"
echo ""
echo -e "Comandos úteis:"
echo -e "  • Ver containers: sshpass -p '${VPS_PASSWORD}' ssh ${VPS_USER}@${VPS_IP} 'cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml ps'"
echo -e "  • Ver logs: sshpass -p '${VPS_PASSWORD}' ssh ${VPS_USER}@${VPS_IP} 'cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml logs -f'"
echo -e "  • Parar: sshpass -p '${VPS_PASSWORD}' ssh ${VPS_USER}@${VPS_IP} 'cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml down'"
echo ""

# Limpar arquivo local
echo -e "${YELLOW}Limpando arquivo local...${NC}"
rm ${LOCAL_ARCHIVE}
echo -e "${GREEN}✓ Arquivo ${LOCAL_ARCHIVE} removido${NC}"
echo ""
