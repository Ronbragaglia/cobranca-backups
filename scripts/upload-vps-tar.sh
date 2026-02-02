#!/bin/bash

# ============================================
# 📤 Script de Upload para VPS usando TAR
# ============================================
# Este script cria um tarball do projeto e faz o upload
# É mais robusto que scp para conexões instáveis
#
# Uso: ./scripts/upload-vps-tar.sh
# ============================================

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurações
VPS_IP="76.13.167.54"
VPS_USER="root"
VPS_PATH="/root/cobranca-api"
LOCAL_PROJECT="/home/admin/projects/cobranca-api"
TEMP_TAR="/tmp/cobranca-api-$(date +%Y%m%d-%H%M%S).tar.gz"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}📤 Upload do Projeto para VPS (TAR)${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Verificar se o projeto existe
if [ ! -d "$LOCAL_PROJECT" ]; then
    echo -e "${RED}❌ Erro: Projeto não encontrado em $LOCAL_PROJECT${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Projeto encontrado em: $LOCAL_PROJECT${NC}"
echo ""

# Mostrar tamanho do projeto
PROJECT_SIZE=$(du -sh "$LOCAL_PROJECT" | cut -f1)
echo -e "${BLUE}📊 Tamanho do projeto: ${PROJECT_SIZE}${NC}"
echo ""

# Mostrar informações do upload
echo -e "${YELLOW}🚀 Iniciando processo de upload:${NC}"
echo -e "   IP: ${VPS_IP}"
echo -e "   Usuário: ${VPS_USER}"
echo -e "   Destino: ${VPS_PATH}"
echo -e "   Projeto: cobranca-api (${PROJECT_SIZE})"
echo ""

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}📦 Passo 1: Criando arquivo TAR...${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Criar o tarball excluindo arquivos desnecessários
echo -e "${YELLOW}📦 Compactando projeto (isso pode levar alguns minutos)...${NC}"
tar --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.git' \
    --exclude='.env' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/app/public/*' \
    -czf "$TEMP_TAR" -C "$LOCAL_PROJECT" .

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Erro ao criar arquivo TAR${NC}"
    exit 1
fi

# Mostrar tamanho do tarball
TAR_SIZE=$(du -sh "$TEMP_TAR" | cut -f1)
echo -e "${GREEN}✅ Arquivo TAR criado: ${TEMP_TAR} (${TAR_SIZE})${NC}"
echo ""

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}📡 Passo 2: Enviando arquivo TAR para VPS...${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Enviar o tarball para a VPS
echo -e "${YELLOW}📡 Enviando ${TAR_SIZE} para a VPS...${NC}"
scp "$TEMP_TAR" "${VPS_USER}@${VPS_IP}:/tmp/"

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Erro ao enviar arquivo TAR para VPS${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Arquivo TAR enviado com sucesso!${NC}"
echo ""

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}📂 Passo 3: Extraindo arquivo TAR na VPS...${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Criar diretório de destino e extrair
echo -e "${YELLOW}📂 Criando diretório e extraindo arquivos...${NC}"
ssh "${VPS_USER}@${VPS_IP}" "mkdir -p ${VPS_PATH} && tar -xzf /tmp/$(basename $TEMP_TAR) -C ${VPS_PATH} && rm /tmp/$(basename $TEMP_TAR)"

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Erro ao extrair arquivo TAR na VPS${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Arquivos extraídos com sucesso!${NC}"
echo ""

# Limpar arquivo tar local
echo -e "${YELLOW}🧹 Limpando arquivo TAR local...${NC}"
rm "$TEMP_TAR"
echo -e "${GREEN}✅ Arquivo TAR local removido${NC}"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Upload concluído com sucesso!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

echo -e "${BLUE}📋 Próximos passos:${NC}"
echo -e "   1. Acessar a VPS:"
echo -e "      ${YELLOW}ssh ${VPS_USER}@${VPS_IP}${NC}"
echo -e ""
echo -e "   2. Navegar até o projeto:"
echo -e "      ${YELLOW}cd ${VPS_PATH}${NC}"
echo -e ""
echo -e "   3. Verificar os arquivos:"
echo -e "      ${YELLOW}ls -la${NC}"
echo -e ""
echo -e "   4. Instalar dependências:"
echo -e "      ${YELLOW}composer install --no-dev --optimize-autoloader${NC}"
echo -e "      ${YELLOW}npm install${NC}"
echo -e "      ${YELLOW}npm run build${NC}"
echo -e ""
echo -e "   5. Configurar o ambiente:"
echo -e "      ${YELLOW}cp .env.example .env${NC}"
echo -e "      ${YELLOW}php artisan key:generate${NC}"
echo -e ""
echo -e "   6. Executar migrações:"
echo -e "      ${YELLOW}php artisan migrate --force${NC}"
echo ""
