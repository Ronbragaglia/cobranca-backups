#!/bin/bash

# Script para resolver problema de conexão MySQL no WSL2
# Uso: bash fix-mysql-wsl2.sh

set -e

echo "=========================================="
echo "  SOLUÇÃO RÁPIDA - MYSQL WSL2"
echo "=========================================="
echo ""

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar se Docker está disponível
echo -e "${YELLOW}[1/6] Verificando Docker...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker não encontrado!${NC}"
    echo ""
    echo "Para resolver, você precisa:"
    echo "1. Instalar o Docker Desktop no Windows"
    echo "2. Abrir o Docker Desktop"
    echo "3. Ir em Settings → Resources → WSL Integration"
    echo "4. Ativar a integração WSL2 para sua distribuição Debian"
    echo "5. Clicar em 'Apply & Restart'"
    echo "6. Fechar e reabrir este terminal WSL2"
    echo ""
    exit 1
fi

echo -e "${GREEN}✅ Docker encontrado!${NC}"
echo ""

# Verificar se docker-compose está disponível
echo -e "${YELLOW}[2/6] Verificando docker-compose...${NC}"
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}❌ docker-compose não encontrado!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ docker-compose encontrado!${NC}"
echo ""

# Navegar para o diretório do projeto
echo -e "${YELLOW}[3/6] Navegando para o diretório do projeto...${NC}"
cd /home/admin/projects/cobranca-api

# Verificar se o arquivo docker-compose.mysql.yml existe
if [ ! -f "docker-compose.mysql.yml" ]; then
    echo -e "${RED}❌ Arquivo docker-compose.mysql.yml não encontrado!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ No diretório do projeto${NC}"
echo ""

# Parar containers existentes (se houver)
echo -e "${YELLOW}[4/6] Parando containers existentes...${NC}"
docker-compose -f docker-compose.mysql.yml down 2>/dev/null || true
echo -e "${GREEN}✅ Containers parados${NC}"
echo ""

# Iniciar o MySQL
echo -e "${YELLOW}[5/6] Iniciando MySQL via Docker...${NC}"
docker-compose -f docker-compose.mysql.yml up -d

# Aguardar o MySQL estar pronto
echo "Aguardando o MySQL iniciar (15 segundos)..."
sleep 15

# Verificar se o container está rodando
if ! docker ps | grep -q cobranca_mysql; then
    echo -e "${RED}❌ Container MySQL não está rodando!${NC}"
    echo ""
    echo "Verificando logs:"
    docker-compose -f docker-compose.mysql.yml logs mysql
    exit 1
fi

echo -e "${GREEN}✅ MySQL iniciado com sucesso!${NC}"
echo ""

# Criar banco de dados
echo -e "${YELLOW}[6/6] Criando banco de dados...${NC}"
docker-compose -f docker-compose.mysql.yml exec -T mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || {
    echo -e "${RED}❌ Erro ao criar banco de dados!${NC}"
    exit 1
}

echo -e "${GREEN}✅ Banco de dados criado com sucesso!${NC}"
echo ""

# Verificar se o banco foi criado
echo "Verificando bancos de dados:"
docker-compose -f docker-compose.mysql.yml exec -T mysql mysql -uroot -proot -e "SHOW DATABASES;" 2>/dev/null || {
    echo -e "${RED}❌ Erro ao listar bancos de dados!${NC}"
    exit 1
}

echo ""
echo "=========================================="
echo -e "${GREEN}✅ MYSQL CONFIGURADO COM SUCESSO!${NC}"
echo "=========================================="
echo ""
echo "Próximos passos:"
echo ""
echo "1. Atualize o arquivo .env.local com:"
echo "   DB_HOST=mysql"
echo "   DB_PORT=3306"
echo "   DB_DATABASE=cobranca"
echo "   DB_USERNAME=root"
echo "   DB_PASSWORD=root"
echo ""
echo "2. Execute as migrações do Laravel:"
echo "   php artisan migrate --force"
echo ""
echo "3. Acesse o phpMyAdmin (opcional):"
echo "   URL: http://localhost:8080"
echo "   Usuário: root"
echo "   Senha: root"
echo ""
echo "Comandos úteis:"
echo "  - Verificar status: docker ps | grep mysql"
echo "  - Ver logs: docker-compose -f docker-compose.mysql.yml logs mysql"
echo "  - Parar MySQL: docker-compose -f docker-compose.mysql.yml down"
echo "  - Reiniciar MySQL: docker-compose -f docker-compose.mysql.yml restart"
echo ""
