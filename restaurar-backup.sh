#!/bin/bash

# ============================================
# SCRIPT DE RESTAURAÇÃO - COBRANCA API
# ============================================
# Este script restaura o backup completo do banco de dados
# e arquivos essenciais do projeto
# ============================================

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}RESTAURAÇÃO - COBRANCA API${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Verificar se foi fornecido o arquivo de backup
if [ -z "$1" ]; then
    echo -e "${RED}Uso: $0 <arquivo_backup.tar.gz>${NC}"
    echo ""
    echo "Exemplo:"
    echo "  $0 backups/cobranca_backup_20260203_085500.tar.gz"
    exit 1
fi

BACKUP_FILE=$1

# Verificar se o arquivo existe
if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}✗ Arquivo não encontrado: $BACKUP_FILE${NC}"
    exit 1
fi

echo -e "${YELLOW}Arquivo de backup: $BACKUP_FILE${NC}"
echo ""

# ============================================
# 1. EXTRAIR ARQUIVO
# ============================================
echo -e "${YELLOW}[1/4] Extraindo arquivo de backup...${NC}"
tar -xzf $BACKUP_FILE

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Erro ao extrair arquivo${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Arquivo extraído${NC}"

# Encontrar o diretório extraído
EXTRACTED_DIR=$(ls -d cobranca_backup_* 2>/dev/null | head -n 1)

if [ -z "$EXTRACTED_DIR" ]; then
    echo -e "${RED}✗ Diretório extraído não encontrado${NC}"
    exit 1
fi

echo -e "${GREEN}Diretório: $EXTRACTED_DIR${NC}"

# ============================================
# 2. RESTAURAR BANCO DE DADOS
# ============================================
echo -e "${YELLOW}[2/4] Restaurando banco de dados...${NC}"

# Verificar se o arquivo SQL existe
if [ ! -f "cobranca_completo.sql" ]; then
    echo -e "${RED}✗ Arquivo cobranca_completo.sql não encontrado${NC}"
    exit 1
fi

echo -e "${YELLOW}Digite a senha do MySQL (root):${NC}"
mysql -u root -p cobranca < cobranca_completo.sql

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Banco de dados restaurado${NC}"
else
    echo -e "${RED}✗ Erro ao restaurar banco de dados${NC}"
    exit 1
fi

# ============================================
# 3. RESTAURAR MIGRAÇÕES
# ============================================
echo -e "${YELLOW}[3/4] Restaurando migrações...${NC}"

if [ -d "$EXTRACTED_DIR/database/migrations" ]; then
    cp -r $EXTRACTED_DIR/database/migrations/*.php database/migrations/
    echo -e "${GREEN}✓ Migrações restauradas${NC}"
else
    echo -e "${YELLOW}⚠ Diretório de migrações não encontrado${NC}"
fi

# ============================================
# 4. RESTAURAR SEEDERS
# ============================================
echo -e "${YELLOW}[4/4] Restaurando seeders...${NC}"

if [ -d "$EXTRACTED_DIR/database/seeders" ]; then
    cp -r $EXTRACTED_DIR/database/seeders/*.php database/seeders/
    echo -e "${GREEN}✓ Seeders restaurados${NC}"
else
    echo -e "${YELLOW}⚠ Diretório de seeders não encontrado${NC}"
fi

# ============================================
# LIMPEZA
# ============================================
echo ""
echo -e "${YELLOW}Limpando arquivos temporários...${NC}"
rm -rf $EXTRACTED_DIR
echo -e "${GREEN}✓ Limpeza concluída${NC}"

# ============================================
# FINALIZAÇÃO
# ============================================
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}RESTAURAÇÃO CONCLUÍDA COM SUCESSO!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Próximos passos:"
echo -e "  1. Verifique o arquivo .env"
echo -e "  2. Execute: php artisan migrate:fresh --seed"
echo -e "  3. Execute: php artisan key:generate"
echo -e "  4. Execute: composer install"
echo -e "  5. Execute: npm install && npm run build"
echo ""
