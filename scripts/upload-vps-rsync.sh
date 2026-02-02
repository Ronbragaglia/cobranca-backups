#!/bin/bash

# ============================================
# 📤 Script de Upload para VPS usando Rsync
# ============================================
# Este script usa rsync para fazer o upload do projeto para a VPS
# Rsync é mais robusto que scp para conexões lentas ou instáveis
#
# Uso: ./scripts/upload-vps-rsync.sh
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

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}📤 Upload do Projeto para VPS (Rsync)${NC}"
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
echo -e "${YELLOW}🚀 Iniciando upload para a VPS:${NC}"
echo -e "   IP: ${VPS_IP}"
echo -e "   Usuário: ${VPS_USER}"
echo -e "   Destino: ${VPS_PATH}"
echo -e "   Projeto: cobranca-api (${PROJECT_SIZE})"
echo ""

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}🚀 Iniciando transferência...${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Opções do rsync:
# -a: modo arquivo (preserva permissões, timestamps, etc.)
# -v: verbose
# -z: compressão durante a transferência
# --progress: mostra o progresso da transferência
# --partial: mantém arquivos parcialmente transferidos
# --partial-dir=.rsync-partial: diretório para arquivos parciais
# --delete: deleta arquivos no destino que não existem na origem
# --exclude: exclui arquivos/diretórios específicos

rsync -avz --progress --partial --partial-dir=.rsync-partial \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.env' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    "$LOCAL_PROJECT/" "${VPS_USER}@${VPS_IP}:${VPS_PATH}/"

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
else
    echo ""
    echo -e "${RED}========================================${NC}"
    echo -e "${RED}❌ Erro durante o upload${NC}"
    echo -e "${RED}========================================${NC}"
    echo ""
    echo -e "${YELLOW}💡 Você pode tentar novamente executando o script novamente${NC}"
    echo -e "${YELLOW}   O rsync continuará de onde parou${NC}"
    echo ""
    exit 1
fi
