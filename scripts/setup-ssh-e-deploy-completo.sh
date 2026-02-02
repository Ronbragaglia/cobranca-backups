#!/bin/bash

# =============================================================================
# SCRIPT COMPLETO: CONFIGURAÇÃO SSH SEM SENHA + DEPLOY AUTOMÁTICO
# VPS: 76.13.167.54 (Ubuntu 22.04, root)
# Senha SSH: 1Qaz2wsx@2026
# =============================================================================

set -e

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
SSH_KEY="$HOME/.ssh/cobranca_deploy"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}CONFIGURAÇÃO SSH + DEPLOY AUTOMÁTICO${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# =============================================================================
# PASSO 1: Gerar chave SSH local
# =============================================================================
echo -e "${YELLOW}[PASSO 1/6] Gerando chave SSH local...${NC}"
if [ -f "$SSH_KEY" ]; then
    echo -e "${YELLOW}Chave SSH já existe, usando existente...${NC}"
else
    ssh-keygen -t rsa -b 4096 -f "$SSH_KEY" -N "" -C "cobranca-auto-deploy" <<< ""
    echo -e "${GREEN}✓ Chave SSH gerada${NC}"
fi

# Ler chave pública
SSH_PUB_KEY=$(cat "$SSH_KEY.pub")
echo -e "${GREEN}✓ Chave pública: ${SSH_PUB_KEY}${NC}"
echo ""

# =============================================================================
# PASSO 2: Configurar chave na VPS
# =============================================================================
echo -e "${YELLOW}[PASSO 2/6] Configurando chave SSH na VPS...${NC}"

# Usar expect para configurar a chave
expect << EOF
set timeout 60
spawn ssh -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "mkdir -p /root/.ssh && echo '${SSH_PUB_KEY}' >> /root/.ssh/authorized_keys && chmod 700 /root/.ssh && chmod 600 /root/.ssh/authorized_keys && systemctl restart ssh && echo '✓ Chave SSH configurada'"
expect {
    "password:" {
        send "${VPS_PASSWORD}\r"
        expect eof
    }
    eof {}
}
EOF
echo -e "${GREEN}✓ Chave SSH configurada na VPS${NC}"
echo ""

# =============================================================================
# PASSO 3: Testar SSH sem senha
# =============================================================================
echo -e "${YELLOW}[PASSO 3/6] Testando SSH sem senha...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "echo '✓ SSH sem senha funcionando!' && exit"
echo -e "${GREEN}✓ SSH sem senha configurado com sucesso${NC}"
echo ""

# =============================================================================
# PASSO 4: Instalar Docker e Docker Compose
# =============================================================================
echo -e "${YELLOW}[PASSO 4/6] Instalando Docker e Docker Compose...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "apt-get update && apt-get install -y docker.io docker-compose && systemctl enable docker && systemctl start docker && docker --version && docker-compose --version"
echo -e "${GREEN}✓ Docker e Docker Compose instalados${NC}"
echo ""

# =============================================================================
# PASSO 5: Transferir e configurar projeto
# =============================================================================
echo -e "${YELLOW}[PASSO 5/6] Transferindo e configurando projeto...${NC}"

# Criar diretório
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "mkdir -p ${VPS_DIR}"

# Compactar projeto
echo -e "${YELLOW}  Compactando projeto...${NC}"
tar -czf ${LOCAL_ARCHIVE} \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.git' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  .

# Transferir arquivo
echo -e "${YELLOW}  Transferindo arquivo para VPS...${NC}"
scp -i "$SSH_KEY" -o StrictHostKeyChecking=no ${LOCAL_ARCHIVE} ${VPS_USER}@${VPS_IP}:/var/www/${LOCAL_ARCHIVE}

# Extrair projeto
echo -e "${YELLOW}  Extraindo projeto na VPS...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "cd /var/www && tar -xzf ${LOCAL_ARCHIVE} -C ${VPS_DIR} && rm ${LOCAL_ARCHIVE} && ls -la ${VPS_DIR}/"

echo -e "${GREEN}✓ Projeto configurado na VPS${NC}"
echo ""

# =============================================================================
# PASSO 6: Configurar firewall e iniciar Docker
# =============================================================================
echo -e "${YELLOW}[PASSO 6/6] Configurando firewall e iniciando containers...${NC}"

# Configurar firewall
echo -e "${YELLOW}  Configurando firewall...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "ufw allow 80/tcp && ufw allow 8080/tcp && ufw allow 22/tcp && ufw --force enable && ufw status"

# Iniciar containers
echo -e "${YELLOW}  Iniciando containers Docker (pode levar 5-10 minutos)...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml up -d --build"

echo -e "${GREEN}✓ Containers iniciados${NC}"
echo ""

# =============================================================================
# VALIDAÇÃO
# =============================================================================
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}VALIDAÇÃO${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

echo -e "${YELLOW}[1/4] Verificando containers...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml ps"
echo ""

echo -e "${YELLOW}[2/4] Testando aplicação na porta 8000...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "curl -I http://localhost:8000"
echo ""

echo -e "${YELLOW}[3/4] Testando phpMyAdmin na porta 8080...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "curl -I http://localhost:8080"
echo ""

echo -e "${YELLOW}[4/4] Verificando logs do app...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml logs --tail=20 app"
echo ""

# =============================================================================
# LIMPEZA
# =============================================================================
echo -e "${YELLOW}Limpando arquivo local...${NC}"
rm -f ${LOCAL_ARCHIVE}
echo -e "${GREEN}✓ Arquivo ${LOCAL_ARCHIVE} removido${NC}"
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
echo -e "  • Ver containers: ssh -i ${SSH_KEY} ${VPS_USER}@${VPS_IP} 'cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml ps'"
echo -e "  • Ver logs: ssh -i ${SSH_KEY} ${VPS_USER}@${VPS_IP} 'cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml logs -f'"
echo -e "  • Parar: ssh -i ${SSH_KEY} ${VPS_USER}@${VPS_IP} 'cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml down'"
echo ""
echo -e "Chave SSH: ${SSH_KEY}"
echo -e "Chave pública: ${SSH_PUB_KEY}"
echo ""
