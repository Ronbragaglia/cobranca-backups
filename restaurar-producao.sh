#!/bin/bash

# ============================================
# SCRIPT DE RESTAURAÇÃO - PRODUÇÃO COBRANCA API
# ============================================
# Credenciais de Produção
# DB: cobranca | user: cobranca | pass: Cobranca@2026
# ============================================

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
echo -e "${GREEN}RESTAURAÇÃO PRODUÇÃO - COBRANCA API${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Banco de dados: ${DB_NAME}"
echo -e "Usuário: ${DB_USER}"
echo ""

# Verificar se foi fornecido o arquivo de backup
if [ -z "$1" ]; then
    echo -e "${RED}Uso: $0 <arquivo_backup.sql ou arquivo_backup.tar.gz>${NC}"
    echo ""
    echo "Exemplos:"
    echo "  $0 backups/cobranca_producao_20260203_091800.sql"
    echo "  $0 backups/cobranca_producao_20260203_091800.tar.gz"
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
# 1. EXTRAIR ARQUIVO (se for .tar.gz)
# ============================================
if [[ "$BACKUP_FILE" == *.tar.gz ]]; then
    echo -e "${YELLOW}[1/4] Extraindo arquivo de backup...${NC}"
    tar -xzf $BACKUP_FILE
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}✗ Erro ao extrair arquivo${NC}"
        exit 1
    fi
    
    echo -e "${GREEN}✓ Arquivo extraído${NC}"
    
    # Encontrar o arquivo SQL extraído
    SQL_FILE=$(ls -1 cobranca_producao_*.sql 2>/dev/null | head -n 1)
    
    if [ -z "$SQL_FILE" ]; then
        echo -e "${RED}✗ Arquivo SQL não encontrado no backup${NC}"
        exit 1
    fi
    
    echo -e "${GREEN}Arquivo SQL: $SQL_FILE${NC}"
else
    SQL_FILE=$BACKUP_FILE
fi

# ============================================
# 2. VERIFICAR BANCO DE DADOS
# ============================================
echo -e "${YELLOW}[2/4] Verificando banco de dados...${NC}"

# Verificar se o banco existe
DB_EXISTS=$(mysql -h ${DB_HOST} -u ${DB_USER} -p${DB_PASS} -e "SHOW DATABASES LIKE '${DB_NAME}';" 2>/dev/null | grep ${DB_NAME})

if [ -z "$DB_EXISTS" ]; then
    echo -e "${YELLOW}⚠ Banco de dados não existe. Criando...${NC}"
    mysql -h ${DB_HOST} -u ${DB_USER} -p${DB_PASS} -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Banco de dados criado${NC}"
    else
        echo -e "${RED}✗ Erro ao criar banco de dados${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}✓ Banco de dados existe${NC}"
fi

# ============================================
# 3. RESTAURAR BANCO DE DADOS
# ============================================
echo -e "${YELLOW}[3/4] Restaurando banco de dados...${NC}"

mysql -h ${DB_HOST} -u ${DB_USER} -p${DB_PASS} ${DB_NAME} < ${SQL_FILE} 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Banco de dados restaurado${NC}"
else
    echo -e "${RED}✗ Erro ao restaurar banco de dados${NC}"
    exit 1
fi

# ============================================
# 4. VERIFICAR TABELAS
# ============================================
echo -e "${YELLOW}[4/4] Verificando tabelas...${NC}"

TABLE_COUNT=$(mysql -h ${DB_HOST} -u ${DB_USER} -p${DB_PASS} ${DB_NAME} -e "SHOW TABLES;" 2>/dev/null | wc -l)

if [ $TABLE_COUNT -gt 0 ]; then
    echo -e "${GREEN}✓ ${TABLE_COUNT} tabelas restauradas${NC}"
else
    echo -e "${YELLOW}⚠ Nenhuma tabela encontrada${NC}"
fi

# ============================================
# LIMPEZA
# ============================================
echo ""
echo -e "${YELLOW}Limpando arquivos temporários...${NC}"

# Remover arquivos SQL temporários extraídos
rm -f cobranca_producao_*.sql 2>/dev/null

echo -e "${GREEN}✓ Limpeza concluída${NC}"

# ============================================
# FINALIZAÇÃO
# ============================================
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}RESTAURAÇÃO CONCLUÍDA COM SUCESSO!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Banco de dados: ${DB_NAME}"
echo -e "Tabelas restauradas: ${TABLE_COUNT}"
echo ""
echo -e "Próximos passos:"
echo -e "  1. Verifique o arquivo .env"
echo -e "  2. Execute: php artisan config:clear"
echo -e "  3. Execute: php artisan cache:clear"
echo -e "  4. Execute: php artisan route:clear"
echo -e "  5. Acesse: https://api.cobrancaauto.com.br/admin/saas/dashboard"
echo ""
echo -e "Login: admin@cobranca.com"
echo -e "Senha: admin123"
echo ""
