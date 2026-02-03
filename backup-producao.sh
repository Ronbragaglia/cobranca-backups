#!/bin/bash

# ============================================
# SCRIPT DE BACKUP - PRODUÇÃO COBRANCA API
# ============================================
# Credenciais de Produção
# DB: cobranca | user: cobranca | pass: Cobranca@2026
# ============================================

# Configurações
DATA=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="./backups"
BACKUP_NAME="cobranca_producao_${DATA}"
BACKUP_FILE="${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"

# Credenciais do banco de dados
DB_NAME="cobranca"
DB_USER="cobranca"
DB_PASS="Cobranca@2026"
DB_HOST="localhost"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}BACKUP PRODUÇÃO - COBRANCA API${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Banco de dados: ${DB_NAME}"
echo -e "Usuário: ${DB_USER}"
echo -e "Data: ${DATA}"
echo ""

# Criar diretório de backup
mkdir -p ${BACKUP_DIR}

# ============================================
# 1. DUMP DO BANCO DE DADOS DE PRODUÇÃO
# ============================================
echo -e "${YELLOW}[1/5] Criando dump do banco de dados de produção...${NC}"

mysqldump -h ${DB_HOST} -u ${DB_USER} -p${DB_PASS} ${DB_NAME} > ${BACKUP_NAME}.sql 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Dump criado: ${BACKUP_NAME}.sql${NC}"
    
    # Mostrar tamanho
    SIZE=$(du -h ${BACKUP_NAME}.sql | cut -f1)
    echo -e "${GREEN}Tamanho: ${SIZE}${NC}"
else
    echo -e "${RED}✗ Erro ao criar dump do banco de dados${NC}"
    echo -e "${YELLOW}Verifique se o MySQL está rodando e as credenciais estão corretas${NC}"
    exit 1
fi

# ============================================
# 2. ARQUIVOS DE MIGRAÇÃO
# ============================================
echo -e "${YELLOW}[2/5] Copiando arquivos de migração...${NC}"
MIGRATIONS_DIR="${BACKUP_NAME}/database/migrations"
mkdir -p ${MIGRATIONS_DIR}
cp -r database/migrations/*.php ${MIGRATIONS_DIR}/ 2>/dev/null
echo -e "${GREEN}✓ Migrações copiadas${NC}"

# ============================================
# 3. ARQUIVOS DE SEEDER
# ============================================
echo -e "${YELLOW}[3/5] Copiando arquivos de seeder...${NC}"
SEEDERS_DIR="${BACKUP_NAME}/database/seeders"
mkdir -p ${SEEDERS_DIR}
cp -r database/seeders/*.php ${SEEDERS_DIR}/ 2>/dev/null
echo -e "${GREEN}✓ Seeders copiados${NC}"

# ============================================
# 4. ARQUIVOS DE CONFIGURAÇÃO
# ============================================
echo -e "${YELLOW}[4/5] Copiando arquivos de configuração...${NC}"
mkdir -p ${BACKUP_NAME}
cp composer.json ${BACKUP_NAME}/ 2>/dev/null
cp .env.example ${BACKUP_NAME}/ 2>/dev/null
cp README.md ${BACKUP_NAME}/ 2>/dev/null
echo -e "${GREEN}✓ Arquivos de configuração copiados${NC}"

# ============================================
# 5. CRIAR ARQUIVO TAR.GZ
# ============================================
echo -e "${YELLOW}[5/5] Criando arquivo compactado...${NC}"
tar -czf ${BACKUP_FILE} ${BACKUP_NAME}.sql ${BACKUP_NAME} 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Backup criado com sucesso: ${BACKUP_FILE}${NC}"
    
    # Mostrar tamanho do arquivo
    SIZE=$(du -h ${BACKUP_FILE} | cut -f1)
    echo -e "${GREEN}Tamanho: ${SIZE}${NC}"
    
    # Limpar diretório temporário
    rm -rf ${BACKUP_NAME}
    
    # Mover SQL para backups
    mv ${BACKUP_NAME}.sql ${BACKUP_DIR}/
    
    echo ""
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}BACKUP DE PRODUÇÃO CONCLUÍDO!${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo -e "Arquivos criados:"
    echo -e "  - ${BACKUP_FILE}"
    echo -e "  - ${BACKUP_DIR}/${BACKUP_NAME}.sql"
    echo ""
    echo -e "Para restaurar:"
    echo -e "  tar -xzf ${BACKUP_FILE}"
    echo -e "  mysql -u cobranca -p cobranca < ${BACKUP_NAME}.sql"
    echo ""
else
    echo -e "${RED}✗ Erro ao criar backup${NC}"
    exit 1
fi
