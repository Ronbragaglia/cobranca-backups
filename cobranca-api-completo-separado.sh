#!/bin/bash
# Script para criar arquivo compactado com tudo separado
# Landing page + Backend + Frontend

echo "📦 Criando arquivo compactado com tudo separado..."
echo ""

# Criar arquivo compactado com tudo incluído
cd /home/admin/projects

# Criar arquivo compactado completo com tudo separado
tar -czf cobranca-api-completo-separado.tar.gz \
    --exclude='cobranca-api/vendor' \
    --exclude='cobranca-api/node_modules' \
    --exclude='cobranca-api/.git' \
    --exclude='cobranca-api/storage/logs/*' \
    --exclude='cobranca-api/storage/framework/cache/*' \
    --exclude='cobranca-api/storage/framework/sessions/*' \
    --exclude='cobranca-api/storage/framework/views/*' \
    --exclude='cobranca-api/*.log' \
    --exclude='cobranca-api/limpar-projeto*.sh' \
    --exclude='cobranca-api/ANALISE_LIMPEZA*.md' \
    cobranca-api

echo "✅ Arquivo compactado criado: cobranca-api-completo-separado.tar.gz"
echo ""

# Mostrar tamanho
TAMANHO=$(du -sh cobranca-api-completo-separado.tar.gz | cut -f1)
echo "📊 Tamanho: $TAMANHO"
echo ""

# Mostrar conteúdo
echo "📋 Conteúdo do arquivo:"
echo "   ✅ Landing page completa (resources/views/landing.blade.php)"
echo "   ✅ Backend Laravel (app/, routes/, config/)"
echo "   ✅ Frontend Next.js (frontend/)"
echo "   ✅ Database (migrations/, seeders/)"
echo "   ✅ Scripts úteis (scripts/)"
echo "   ✅ Documentação essencial"
echo ""

echo "💚 Arquivo pronto para uso! 💸"
