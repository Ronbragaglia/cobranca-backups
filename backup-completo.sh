#!/bin/bash

# ============================================
# SCRIPT DE BACKUP COMPLETO - COBRANCA API
# ============================================
# Este script cria um backup completo do banco de dados
# e arquivos essenciais do projeto
# ============================================

# Configurações
DATA=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="./backups"
BACKUP_NAME="cobranca_backup_${DATA}"
BACKUP_FILE="${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}BACKUP COMPLETO - COBRANCA API${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Criar diretório de backup
mkdir -p ${BACKUP_DIR}

# ============================================
# 1. DUMP DO BANCO DE DADOS
# ============================================
echo -e "${YELLOW}[1/5] Criando dump do banco de dados...${NC}"

# Verificar se o arquivo SQL existe
if [ -f "cobranca_completo.sql" ]; then
    echo -e "${GREEN}✓ Arquivo cobranca_completo.sql encontrado${NC}"
else
    echo -e "${RED}✗ Arquivo cobranca_completo.sql não encontrado${NC}"
    exit 1
fi

# ============================================
# 2. ARQUIVOS DE MIGRAÇÃO
# ============================================
echo -e "${YELLOW}[2/5] Copiando arquivos de migração...${NC}"
MIGRATIONS_DIR="${BACKUP_NAME}/database/migrations"
mkdir -p ${MIGRATIONS_DIR}
cp -r database/migrations/*.php ${MIGRATIONS_DIR}/
echo -e "${GREEN}✓ Migrações copiadas${NC}"

# ============================================
# 3. ARQUIVOS DE SEEDER
# ============================================
echo -e "${YELLOW}[3/5] Copiando arquivos de seeder...${NC}"
SEEDERS_DIR="${BACKUP_NAME}/database/seeders"
mkdir -p ${SEEDERS_DIR}
cp -r database/seeders/*.php ${SEEDERS_DIR}/
echo -e "${GREEN}✓ Seeders copiados${NC}"

# ============================================
# 4. ARQUIVOS DE CONFIGURAÇÃO
# ============================================
echo -e "${YELLOW}[4/5] Copiando arquivos de configuração...${NC}"
mkdir -p ${BACKUP_NAME}
cp composer.json ${BACKUP_NAME}/
cp .env.example ${BACKUP_NAME}/
cp README.md ${BACKUP_NAME}/
echo -e "${GREEN}✓ Arquivos de configuração copiados${NC}"

# ============================================
# 5. CRIAR ARQUIVO TAR.GZ
# ============================================
echo -e "${YELLOW}[5/5] Criando arquivo compactado...${NC}"
tar -czf ${BACKUP_FILE} ${BACKUP_NAME} cobranca_completo.sql

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Backup criado com sucesso: ${BACKUP_FILE}${NC}"
    
    # Mostrar tamanho do arquivo
    SIZE=$(du -h ${BACKUP_FILE} | cut -f1)
    echo -e "${GREEN}Tamanho: ${SIZE}${NC}"
    
    # Limpar diretório temporário
    rm -rf ${BACKUP_NAME}
    
    echo ""
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}BACKUP CONCLUÍDO COM SUCESSO!${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo -e "Arquivo: ${BACKUP_FILE}"
    echo -e "Para restaurar:"
    echo -e "  tar -xzf ${BACKUP_FILE}"
    echo -e "  mysql -u root -p cobranca < cobranca_completo.sql"
    echo ""
else
    echo -e "${RED}✗ Erro ao criar backup${NC}"
    exit 1
fi
