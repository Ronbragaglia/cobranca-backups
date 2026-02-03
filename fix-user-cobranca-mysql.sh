#!/bin/bash

# Script para criar usuário cobranca no MySQL e executar migrate:fresh
# Execute este script na VPS: srv1298946 (76.13.167.54)

echo "=== CRIANDO USUÁRIO COBRANCA NO MYSQL ==="

# Executar comandos MySQL
mysql -u root -proot <<EOF
CREATE USER 'cobranca'@'localhost' IDENTIFIED BY 'root';
GRANT ALL PRIVILEGES ON cobranca.* TO 'cobranca'@'localhost';
FLUSH PRIVILEGES;
EOF

if [ $? -eq 0 ]; then
    echo "✓ Usuário cobranca criado com sucesso!"
else
    echo "✗ Erro ao criar usuário cobranca"
    exit 1
fi

echo ""
echo "=== EXECUTANDO MIGRATE:FRESH COM SEED ==="

# Navegar para o diretório do projeto
cd /var/www/cobranca-api

# Executar migrate:fresh com seed
php artisan migrate:fresh --seed --force

if [ $? -eq 0 ]; then
    echo "✓ Migrate:fresh executado com sucesso!"
else
    echo "✗ Erro ao executar migrate:fresh"
    exit 1
fi

echo ""
echo "=== CONCLUÍDO COM SUCESSO ==="
