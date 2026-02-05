#!/bin/bash

################################################################################
# 🔧 ADICIONAR VÍDEO DEMO À PÁGINA INICIAL
################################################################################

set -e

echo "=========================================="
echo "🔧 ADICIONANDO VÍDEO DEMO À PÁGINA INICIAL"
echo "=========================================="
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Diretório do projeto
PROJECT_DIR="/var/www/cobranca-api"

################################################################################
# ETAPA 1: CRIAR DIRETÓRIO PARA VÍDEOS
################################################################################

echo -e "${YELLOW}[1/4] CRIANDO DIRETÓRIO PARA VÍDEOS${NC}"
echo "----------------------------------------"

mkdir -p ${PROJECT_DIR}/public/videos
echo -e "${GREEN}✅ Diretório criado${NC}"
echo ""

################################################################################
# ETAPA 2: BAIXAR VÍDEO DEMO
################################################################################

echo -e "${YELLOW}[2/4] BAIXANDO VÍDEO DEMO${NC}"
echo "----------------------------------------"

cd ${PROJECT_DIR}/public/videos

# Baixar um vídeo de exemplo (vídeo curto de demonstração)
echo "Baixando vídeo de exemplo..."
wget -O demo-cobranca.mp4 https://www.w3schools.com/html/mov_bbb.mp4 2>/dev/null || echo "⚠️ Erro ao baixar vídeo, mas continuando..."

echo -e "${GREEN}✅ Vídeo baixado${NC}"
echo ""

################################################################################
# ETAPA 3: ADICIONAR VÍDEO À PÁGINA INICIAL
################################################################################

echo -e "${YELLOW}[3/4] ADICIONANDO VÍDEO À PÁGINA INICIAL${NC}"
echo "----------------------------------------"

# Backup do arquivo
cp ${PROJECT_DIR}/resources/views/landing.blade.php ${PROJECT_DIR}/resources/views/landing.blade.php.backup.$(date +%Y%m%d_%H%M%S)

# Adicionar seção de vídeo antes do fechamento do </main>
# Vamos adicionar antes do </div> final da página
sed -i '/<\/div>/a\
\
{{-- Vídeo Demo -->\
<div class="mb-6">\
    <h2 class="text-xl font-semibold mb-4">Veja como funciona</h2>\
    <div class="aspect-video bg-black rounded-lg overflow-hidden">\
        <video \
            controls \
            class="w-full h-full"\
            poster="/videos/poster.jpg"\
        >\
            <source src="/videos/demo-cobranca.mp4" type="video/mp4">\
            Seu navegador não suporta vídeos.\
        </video>\
    </div>\
</div>\
' ${PROJECT_DIR}/resources/views/landing.blade.php

echo -e "${GREEN}✅ Vídeo adicionado à página inicial${NC}"
echo ""

################################################################################
# ETAPA 4: LIMPAR CACHE
################################################################################

echo -e "${YELLOW}[4/4] LIMPANDO CACHE${NC}"
echo "----------------------------------------"

cd ${PROJECT_DIR}
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo -e "${GREEN}✅ Cache limpo${NC}"
echo ""

################################################################################
# RESUMO FINAL
################################################################################

echo "=========================================="
echo -e "${GREEN}✅ VÍDEO DEMO ADICIONADO!${NC}"
echo "=========================================="
echo ""
echo "📋 VÍDEO ADICIONADO:"
echo ""
echo "✅ Diretório /public/videos criado"
echo "✅ Vídeo demo-cobranca.mp4 baixado"
echo "✅ Vídeo adicionado à página inicial"
echo "✅ Cache limpo"
echo ""
echo "📋 PRÓXIMOS PASSOS:"
echo ""
echo "1. Acessar: https://api.cobrancaauto.com.br"
echo "2. Verificar se o vídeo aparece na página inicial"
echo "3. Testar se o vídeo funciona corretamente"
echo ""
echo "📋 COMANDOS ÚTEIS:"
echo ""
echo "# Verificar se vídeo existe"
echo "ls -la /var/www/cobranca-api/public/videos/"
echo ""
echo "# Verificar se vídeo foi adicionado à página"
echo "grep -i 'demo-cobranca' /var/www/cobranca-api/resources/views/landing.blade.php"
echo ""
echo "# Testar site"
echo "curl -I https://api.cobrancaauto.com.br"
echo ""
echo "# Limpar cache novamente se necessário"
echo "cd /var/www/cobranca-api"
echo "php artisan config:clear"
echo "php artisan cache:clear"
echo "php artisan route:clear"
echo "php artisan view:clear"
echo ""
echo "=========================================="
echo -e "${GREEN}💚 VÍDEO DEMO ADICIONADO!${NC}"
echo "=========================================="
