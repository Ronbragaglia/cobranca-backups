#!/bin/bash

# =============================================================================
# SCRIPT DEPLOY COMPLETO - EXECUÇÃO MANUAL
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
echo -e "${GREEN}DEPLOY COMPLETO - VPS HOSTINGER${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# =============================================================================
# PASSO 1: Configurar chave SSH na VPS
# =============================================================================
echo -e "${YELLOW}[PASSO 1/11] Configurando chave SSH na VPS...${NC}"
echo -e "${YELLOW}Digite a senha quando solicitado: ${VPS_PASSWORD}${NC}"
echo ""

ssh -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "mkdir -p /root/.ssh && echo 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDEOcC7bXcpN9NszSVCnHmmrXktf2yyALI+VGnMd6eGgmaA9uBz3KhR838HqcatX7YNPp40tHPhooxys71mfVaRA6DmrHcgwwAF9Hm0L7GM7HHW90vWI11+wzonebj8R17+rVWbg2VBgSI5wNiHmaYxRvVf/hBgJ4hOmUpC9OSi46btTbwRHekY2AO7hqBHqGh6m7xoy0Bx/Leuq40EvlCoiOnkE6aklHnILPI4gqmkDoNN33cacTBnMnb1gSc14yd9xQh3n8wP7LtG7JKD/BQgwMLLHTEcaS1/rg/turPNhcKrRV/ZSjb+P1Tzc06yO7FQUHGwIimq1FHyRJETiN81Wt3XNsoUqF9oD2YJMmQCo2mptBbvVa8HodMyE0zVl3/WQzvZt3k+NVIZoIw0Kn6tRbfiSNRjkHBOfrf20UvB7yAlWotf9/c1x43B8z0lhgWJtF4AHSX1Sh8i+ilTlMcDCLh2SXZCatdDm+n0G7qG4N/Nz1GW9SAZ8Heh2eD11g6jNTJaGufWJGRoOZ77bNJJzkASZOpmhJUE4dS/tShZY9cG+ncBzYwdrVa3l3N4zcCGMFcHkRDFZZLsPOTuZ5TsuiKuqAqh3o+SFyqNwZs2acyh34We+CkzuqBU/JFIeaBYk1hV/cAFAQkxops4RiYmMWShXu/N5EauHfj/YKlnyw== cobranca-auto-deploy' >> /root/.ssh/authorized_keys && chmod 700 /root/.ssh && chmod 600 /root/.ssh/authorized_keys && systemctl restart ssh && echo '✓ Chave SSH configurada!' && exit"

echo -e "${GREEN}✓ PASSO 1 concluído${NC}"
echo ""

# =============================================================================
# PASSO 2: Testar SSH sem senha
# =============================================================================
echo -e "${YELLOW}[PASSO 2/11] Testando SSH sem senha...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "echo '✓ SSH sem senha funcionando!' && exit"
echo -e "${GREEN}✓ PASSO 2 concluído${NC}"
echo ""

# =============================================================================
# PASSO 3: Instalar Docker e Docker Compose
# =============================================================================
echo -e "${YELLOW}[PASSO 3/11] Instalando Docker e Docker Compose...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "apt-get update && apt-get install -y docker.io docker-compose && systemctl enable docker && systemctl start docker && docker --version && docker-compose --version"
echo -e "${GREEN}✓ PASSO 3 concluído${NC}"
echo ""

# =============================================================================
# PASSO 4: Criar diretório do projeto
# =============================================================================
echo -e "${YELLOW}[PASSO 4/11] Criando diretório do projeto na VPS...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "mkdir -p ${VPS_DIR} && ls -la /var/www/"
echo -e "${GREEN}✓ PASSO 4 concluído${NC}"
echo ""

# =============================================================================
# PASSO 5: Compactar projeto local
# =============================================================================
echo -e "${YELLOW}[PASSO 5/11] Compactando projeto local...${NC}"
tar -czf ${LOCAL_ARCHIVE} \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.git' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  .
echo -e "${GREEN}✓ PASSO 5 concluído - Arquivo: ${LOCAL_ARCHIVE}${NC}"
echo ""

# =============================================================================
# PASSO 6: Transferir arquivo para VPS
# =============================================================================
echo -e "${YELLOW}[PASSO 6/11] Transferindo arquivo para VPS...${NC}"
scp -i "$SSH_KEY" -o StrictHostKeyChecking=no ${LOCAL_ARCHIVE} ${VPS_USER}@${VPS_IP}:/var/www/${LOCAL_ARCHIVE}
echo -e "${GREEN}✓ PASSO 6 concluído${NC}"
echo ""

# =============================================================================
# PASSO 7: Extrair projeto na VPS
# =============================================================================
echo -e "${YELLOW}[PASSO 7/11] Extraindo projeto na VPS...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "cd /var/www && tar -xzf ${LOCAL_ARCHIVE} -C ${VPS_DIR} && rm ${LOCAL_ARCHIVE} && ls -la ${VPS_DIR}/"
echo -e "${GREEN}✓ PASSO 7 concluído${NC}"
echo ""

# =============================================================================
# PASSO 8: Configurar firewall
# =============================================================================
echo -e "${YELLOW}[PASSO 8/11] Configurando firewall...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "ufw allow 80/tcp && ufw allow 8080/tcp && ufw allow 22/tcp && ufw --force enable && ufw status"
echo -e "${GREEN}✓ PASSO 8 concluído${NC}"
echo ""

# =============================================================================
# PASSO 9: Iniciar Docker Compose
# =============================================================================
echo -e "${YELLOW}[PASSO 9/11] Iniciando containers Docker...${NC}"
echo -e "${YELLOW}⚠️  Este comando pode levar 5-10 minutos (build das imagens)${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml up -d --build"
echo -e "${GREEN}✓ PASSO 9 concluído${NC}"
echo ""

# =============================================================================
# PASSO 10: Testar aplicação
# =============================================================================
echo -e "${YELLOW}[PASSO 10/11] Testando aplicação...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "sleep 15 && docker ps && curl -I http://localhost:8000 && echo '---' && curl -I http://localhost:8080"
echo -e "${GREEN}✓ PASSO 10 concluído${NC}"
echo ""

# =============================================================================
# PASSO 11: Ver logs de validação
# =============================================================================
echo -e "${YELLOW}[PASSO 11/11] Verificando logs...${NC}"
ssh -i "$SSH_KEY" -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} "cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml logs --tail=50 app && echo '---' && docker-compose -f docker-compose.dev.yml logs --tail=20 mysql"
echo -e "${GREEN}✓ PASSO 11 concluído${NC}"
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
