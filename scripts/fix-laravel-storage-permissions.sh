#!/bin/bash
# Script para corrigir permissões do storage do Laravel
# Execute este script na VPS (IP: 76.13.167.54)

set -e

echo "=========================================="
echo "🔧 CORRIGINDO PERMISSÕES DO STORAGE LARAVEL"
echo "=========================================="
echo ""

# Definir caminho do projeto
PROJECT_PATH="/var/www/html/cobranca-api"

# Ir para o diretório do projeto
cd "$PROJECT_PATH" || {
    echo "❌ Erro: Diretório $PROJECT_PATH não encontrado"
    exit 1
}

echo "📁 Diretório do projeto: $PROJECT_PATH"
echo ""

# Passo 1: Configurar permissões de storage
echo "=========================================="
echo "📋 PASSO 1: Configurando permissões de storage"
echo "=========================================="
echo ""

echo "🔧 Configurando permissões de storage/..."
chmod -R 775 storage/
echo "✅ Permissões de storage/ configuradas para 775"

echo ""
echo "🔧 Configurando permissões de storage/logs/..."
chmod 777 storage/logs/
echo "✅ Permissões de storage/logs/ configuradas para 777"

echo ""
echo "🔧 Configurando permissões de storage/framework/..."
chmod -R 775 storage/framework/
echo "✅ Permissões de storage/framework/ configuradas para 775"

echo ""
echo "🔧 Configurando permissões de storage/app/..."
chmod -R 775 storage/app/
echo "✅ Permissões de storage/app/ configuradas para 775"

echo ""
echo "🔧 Configurando permissões de bootstrap/cache/..."
chmod -R 775 bootstrap/cache/
echo "✅ Permissões de bootstrap/cache/ configuradas para 775"

# Passo 2: Configurar owner
echo "=========================================="
echo "📋 PASSO 2: Configurando owner"
echo "=========================================="
echo ""

echo "👤 Configurando owner de storage/..."
chown -R www-data:www-data storage/
echo "✅ Owner de storage/ configurado para www-data:www-data"

echo ""
echo "👤 Configurando owner de bootstrap/cache/..."
chown -R www-data:www-data bootstrap/cache/
echo "✅ Owner de bootstrap/cache/ configurado para www-data:www-data"

# Passo 3: Limpar cache do Laravel
echo "=========================================="
echo "📋 PASSO 3: Limpando cache do Laravel"
echo "=========================================="
echo ""

echo "🔄 Limpando cache de configuração..."
php artisan config:cache
echo "✅ Cache de configuração limpo"

echo ""
echo "🔄 Limpando cache de rotas..."
php artisan route:cache
echo "✅ Cache de rotas limpo"

echo ""
echo "🔄 Limpando cache de views..."
php artisan view:cache
echo "✅ Cache de views limpo"

echo ""
echo "🔄 Limpando cache de aplicação..."
php artisan cache:clear
echo "✅ Cache de aplicação limpo"

# Passo 4: Reiniciar serviços
echo "=========================================="
echo "📋 PASSO 4: Reiniciando serviços"
echo "=========================================="
echo ""

echo "🚀 Reiniciando PHP-FPM..."
systemctl restart php8.1-fpm

echo ""
echo "🚀 Reiniciando Nginx..."
systemctl restart nginx

echo ""
echo "📋 Status do PHP-FPM:"
systemctl status php8.1-fpm --no-pager | head -10

echo ""
echo "📋 Status do Nginx:"
systemctl status nginx --no-pager | head -10

# Passo 5: Verificar permissões
echo "=========================================="
echo "📋 PASSO 5: Verificando permissões"
echo "=========================================="
echo ""

echo "📋 Permissões de storage/:"
ls -la storage/ | head -10

echo ""
echo "📋 Permissões de storage/logs/:"
ls -la storage/logs/ | head -10

echo ""
echo "📋 Permissões de bootstrap/cache/:"
ls -la bootstrap/cache/ | head -10

# Passo 6: Verificar logs
echo "=========================================="
echo "📋 PASSO 6: Verificando logs"
echo "=========================================="
echo ""

echo "📋 Últimas 20 linhas do log do Laravel:"
tail -20 storage/logs/laravel.log

echo ""
echo "📋 Últimas 20 linhas do log de erro do Nginx:"
tail -20 /var/log/nginx/error.log

echo ""
echo "=========================================="
echo "✅ PROCESSO CONCLUÍDO!"
echo "=========================================="
echo ""
echo "🎉 Resumo:"
echo "  ✅ Permissões de storage configuradas"
echo "  ✅ Permissões de bootstrap/cache configuradas"
echo "  ✅ Owner configurado para www-data:www-data"
echo "  ✅ Cache do Laravel limpo"
echo "  ✅ PHP-FPM reiniciado"
echo "  ✅ Nginx reiniciado"
echo ""
echo "📝 Próximos passos:"
echo "  1. Teste a aplicação em: https://api.cobrancaauto.com.br/admin/saas/dashboard"
echo "  2. Verifique os logs se ainda houver erros"
echo ""
