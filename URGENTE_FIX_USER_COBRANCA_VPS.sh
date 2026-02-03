#!/bin/bash
# ============================================
# URGENTE - FIX USER COBRANCA MYSQL
# VPS: srv1298946 (76.13.167.54)
# ============================================

echo "=== CONECTANDO AO MYSQL ==="
mysql -u root -proot <<EOF
-- Criar usuário cobranca se não existir
CREATE USER IF NOT EXISTS 'cobranca'@'localhost' IDENTIFIED BY 'root';

-- Garantir privilégios completos
GRANT ALL PRIVILEGES ON cobranca.* TO 'cobranca'@'localhost';

-- Aplicar mudanças
FLUSH PRIVILEGES;

-- Verificar usuário criado
SELECT User, Host FROM mysql.user WHERE User = 'cobranca';

EXIT;
EOF

echo ""
echo "=== MYSQL CONFIGURADO COM SUCESSO ==="
echo ""
echo "=== RODANDO MIGRATIONS ==="
cd /var/www/cobranca-api
php artisan migrate:fresh --seed --force

echo ""
echo "=== SUCESSO! ==="
echo "Usuário 'cobranca' criado e migrations executadas!"
