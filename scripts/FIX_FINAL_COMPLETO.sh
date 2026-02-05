#!/bin/bash

################################################################################
# 🔥 FIX FINAL COMPLETO - RESOLVER TODOS OS PROBLEMAS
# Execute na VPS: ssh root@76.13.167.54
################################################################################

set -e

echo "=========================================="
echo "🔥 FIX FINAL COMPLETO"
echo "=========================================="
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

################################################################################
# ETAPA 1: REMOVER CONFIGURAÇÕES DUPLICADAS NGINX
################################################################################
echo -e "${YELLOW}[1/8] REMOVENDO CONFIGURAÇÕES DUPLICADAS NGINX${NC}"
echo "----------------------------------------"

# Remover default se existir
rm -f /etc/nginx/sites-enabled/default 2>/dev/null

# Garantir apenas um link simbólico
ln -sf /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-enabled/cobranca-api

echo -e "${GREEN}✅ Configurações duplicadas removidas${NC}"
echo ""

################################################################################
# ETAPA 2: CRIAR USUÁRIO ADMIN VIA ARQUIVO PHP
################################################################################
echo -e "${YELLOW}[2/8] CRIANDO USUÁRIO ADMIN${NC}"
echo "----------------------------------------"

cat > /tmp/criar_admin.php << 'PHPEOF'
<?php
require __DIR__ . '/vendor/autoload.php';
\$app = require_once __DIR__ . '/bootstrap/app.php';
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Criar tenant
\$tenant = Tenant::firstOrCreate([
    'subdomain' => 'principal',
], [
    'name' => 'Principal',
    'subscription_status' => 'active',
]);

// Criar ou atualizar usuário
\$user = User::updateOrCreate(
    ['email' => 'admin@cobranca.com'],
    [
        'name' => 'Admin',
        'password' => Hash::make('123456'),
        'email_verified_at' => now(),
        'tenant_id' => \$tenant->id,
    ]
);

echo "Usuário criado/atualizado com sucesso!\n";
echo "Email: admin@cobranca.com\n";
echo "Senha: 123456\n";
echo "Tenant ID: " . \$user->tenant_id . "\n";
PHPEOF

php /tmp/criar_admin.php
echo -e "${GREEN}✅ Usuário admin criado/atualizado${NC}"
echo ""

################################################################################
# ETAPA 3: LIMPAR CACHE LARAVEL
################################################################################
echo -e "${YELLOW}[3/8] LIMPANDO CACHE LARAVEL${NC}"
echo "----------------------------------------"

cd /var/www/cobranca-api

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
php artisan optimize:clear

echo -e "${GREEN}✅ Cache limpo${NC}"
echo ""

################################################################################
# ETAPA 4: LIMPAR SESSÕES
################################################################################
echo -e "${YELLOW}[4/8] LIMPANDO SESSÕES${NC}"
echo "----------------------------------------"

rm -rf storage/framework/sessions/*
mkdir -p storage/framework/sessions
chmod -R 775 storage/framework/sessions
chown -R www-data:www-data storage/framework/sessions

echo -e "${GREEN}✅ Sessões limpas${NC}"
echo ""

################################################################################
# ETAPA 5: CORRIGIR PERMISSÕES STORAGE
################################################################################
echo -e "${YELLOW}[5/8] CORRIGINDO PERMISSÕES STORAGE${NC}"
echo "----------------------------------------"

chmod -R 775 storage/
chown -R www-data:www-data storage/

echo -e "${GREEN}✅ Permissões corrigidas${NC}"
echo ""

################################################################################
# ETAPA 6: TESTAR NGINX
################################################################################
echo -e "${YELLOW}[6/8] TESTANDO NGINX${NC}"
echo "----------------------------------------"

nginx -t

echo -e "${GREEN}✅ NGINX testado${NC}"
echo ""

################################################################################
# ETAPA 7: REINICIAR SERVIÇOS
################################################################################
echo -e "${YELLOW}[7/8] REINICIANDO SERVIÇOS${NC}"
echo "----------------------------------------"

systemctl restart php8.2-fpm
systemctl restart nginx

echo -e "${GREEN}✅ Serviços reiniciados${NC}"
echo ""

################################################################################
# ETAPA 8: VERIFICAR STATUS
################################################################################
echo -e "${YELLOW}[8/8] VERIFICANDO STATUS${NC}"
echo "----------------------------------------"

systemctl status php8.2-fpm --no-pager | head -10
systemctl status nginx --no-pager | head -10

echo ""

################################################################################
# ETAPA 9: TESTAR SITE
################################################################################
echo -e "${YELLOW}[9/9] TESTANDO SITE${NC}"
echo "----------------------------------------"

echo "📌 Testando HTTP:"
curl -I http://api.cobrancaauto.com.br/ 2>&1 | head -10

echo ""
echo "📌 Testando login:"
curl -X POST http://api.cobrancaauto.com.br/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@cobranca.com","password":"123456"}' \
  2>&1 | head -10

echo ""

################################################################################
# RESUMO FINAL
################################################################################
echo "=========================================="
echo -e "${GREEN}✅ FIX FINAL CONCLUÍDO!${NC}"
echo "=========================================="
echo ""
echo "📌 CREDENCIAIS DE ACESSO:"
echo "   URL: http://api.cobrancaauto.com.br/login"
echo "   Email: admin@cobranca.com"
echo "   Senha: 123456"
echo ""
echo "📌 PRÓXIMOS PASSOS:"
echo "   1. Limpe o cache do navegador (Ctrl + Shift + Delete)"
echo "   2. Acesse o site no navegador"
echo "   3. Faça login com as credenciais acima"
echo "   4. Se ainda não funcionar, verifique os logs:"
echo "      tail -50 /var/www/cobranca-api/storage/logs/laravel.log"
echo "      tail -50 /var/log/nginx/error.log"
echo "      tail -50 /var/log/php8.2-fpm.log"
echo ""
echo "=========================================="
echo -e "${GREEN}💚 Site deve estar funcionando agora!${NC}"
echo "=========================================="
