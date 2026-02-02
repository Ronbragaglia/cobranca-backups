#!/bin/bash

# ============================================
# 📤 Script de Upload para VPS - Cobranca API
# ============================================
# Este script faz o upload do projeto para a VPS de produção
# 
# Uso: ./scripts/upload-vps.sh
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
VPS_PATH="/root/"
LOCAL_PROJECT="/home/admin/projects/cobranca-api"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}📤 Upload do Projeto para VPS${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Verificar se estamos no diretório correto
CURRENT_DIR=$(pwd)
if [ "$CURRENT_DIR" != "/home/admin/projects" ]; then
    echo -e "${YELLOW}⚠️  Você está em: $CURRENT_DIR${NC}"
    echo -e "${YELLOW}📂 Navegando para /home/admin/projects...${NC}"
    cd /home/admin/projects || exit 1
fi

# Verificar se o projeto existe
if [ ! -d "cobranca-api" ]; then
    echo -e "${RED}❌ Erro: Projeto 'cobranca-api' não encontrado em $(pwd)${NC}"
    echo -e "${YELLOW}📂 Listando diretórios disponíveis:${NC}"
    ls -la
    exit 1
fi

echo -e "${GREEN}✅ Projeto encontrado em: $(pwd)/cobranca-api${NC}"
echo ""

# Mostrar tamanho do projeto
PROJECT_SIZE=$(du -sh cobranca-api | cut -f1)
echo -e "${BLUE}📊 Tamanho do projeto: ${PROJECT_SIZE}${NC}"
echo ""

# Mostrar informações do upload
echo -e "${YELLOW}🚀 Pronto para fazer upload para a VPS:${NC}"
echo -e "   IP: ${VPS_IP}"
echo -e "   Usuário: ${VPS_USER}"
echo -e "   Destino: ${VPS_PATH}"
echo -e "   Projeto: cobranca-api (${PROJECT_SIZE})"
echo ""

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}🚀 Iniciando upload...${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Executar o upload
echo -e "${YELLOW}📡 Conectando à VPS e iniciando transferência...${NC}"
echo -e "${YELLOW}⚠️  Se for a primeira conexão, responda 'yes' quando solicitado${NC}"
echo -e "${YELLOW}🔑 Será solicitada a senha do usuário root${NC}"
echo ""

scp -r cobranca-api ${VPS_USER}@${VPS_IP}:${VPS_PATH}

# Verificar resultado
if [ $? -eq 0 ]; then
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
    echo -e "      ${YELLOW}cd ${VPS_PATH}cobranca-api${NC}"
    echo -e ""
    echo -e "   3. Verificar os arquivos:"
    echo -e "      ${YELLOW}ls -la${NC}"
    echo ""
    echo -e "   4. Continuar com a configuração de produção"
    echo ""
else
    echo ""
    echo -e "${RED}========================================${NC}"
    echo -e "${RED}❌ Erro durante o upload${NC}"
    echo -e "${RED}========================================${NC}"
    echo ""
    echo -e "${YELLOW}💡 Se a conexão foi interrompida, você pode usar rsync para continuar:${NC}"
    echo -e "   ${YELLOW}rsync -avz --progress ${LOCAL_PROJECT}/ ${VPS_USER}@${VPS_IP}:${VPS_PATH}cobranca-api/${NC}"
    echo ""
    exit 1
fi
