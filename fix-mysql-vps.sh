#!/bin/bash

# Script para resolver problema de conexão MySQL na VPS Ubuntu
# Uso: sudo bash fix-mysql-vps.sh

set -e

echo "=========================================="
echo "  SOLUÇÃO RÁPIDA - MYSQL VPS"
echo "=========================================="
echo ""

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ Este script precisa ser executado como root (sudo)${NC}"
    echo "Execute: sudo bash fix-mysql-vps.sh"
    exit 1
fi

# Atualizar o sistema
echo -e "${YELLOW}[1/6] Atualizando o sistema...${NC}"
apt update
echo -e "${GREEN}✅ Sistema atualizado${NC}"
echo ""

# Instalar MySQL Server e PHP MySQL extension
echo -e "${YELLOW}[2/6] Instalando MySQL Server e PHP MySQL extension...${NC}"
apt install -y mysql-server php8.1-mysql
echo -e "${GREEN}✅ MySQL Server e PHP MySQL extension instalados${NC}"
echo ""

# Iniciar o serviço MySQL
echo -e "${YELLOW}[3/6] Iniciando o serviço MySQL...${NC}"
systemctl start mysql
echo -e "${GREEN}✅ MySQL iniciado${NC}"
echo ""

# Habilitar o serviço MySQL para iniciar no boot
echo -e "${YELLOW}[4/6] Habilitando MySQL para iniciar no boot...${NC}"
systemctl enable mysql
echo -e "${GREEN}✅ MySQL habilitado para iniciar no boot${NC}"
echo ""

# Verificar status do MySQL
echo -e "${YELLOW}[5/6] Verificando status do MySQL...${NC}"
systemctl status mysql --no-pager
echo ""

# Criar banco de dados cobranca
echo -e "${YELLOW}[6/6] Criando banco de dados cobranca...${NC}"
mysql -u root << EOF
CREATE DATABASE IF NOT EXISTS cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
FLUSH PRIVILEGES;
EOF

echo -e "${GREEN}✅ Banco de dados cobranca criado com sucesso${NC}"
echo ""

# Verificar se o banco foi criado
echo "Verificando bancos de dados:"
mysql -u root -e "SHOW DATABASES;"
echo ""

echo "=========================================="
echo -e "${GREEN}✅ MYSQL CONFIGURADO COM SUCESSO!${NC}"
echo "=========================================="
echo ""

echo "Próximos passos:"
echo ""
echo "1. Navegue até o diretório do projeto:"
echo "   cd /var/www/cobranca-api"
echo ""
echo "2. Execute as migrações do Laravel:"
echo "   php artisan migrate:fresh --seed --force"
echo ""
echo "3. Verifique se o MySQL está rodando:"
echo "   systemctl status mysql"
echo ""
echo "Comandos úteis:"
echo "  - Ver status: systemctl status mysql"
echo "  - Parar MySQL: systemctl stop mysql"
echo "  - Iniciar MySQL: systemctl start mysql"
echo "  - Reiniciar MySQL: systemctl restart mysql"
echo "  - Acessar MySQL: mysql -u root"
echo ""
