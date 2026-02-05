#!/bin/bash

# Script para ajustar a posição do vídeo na página inicial
# Executar na VPS

set -e

echo "🔧 Ajustando posição do vídeo na página inicial..."

cd /var/www/cobranca-api

# Backup do arquivo
BACKUP_FILE="resources/views/landing.blade.php.backup.$(date +%Y%m%d_%H%M%S)"
echo "📦 Criando backup: $BACKUP_FILE"
cp resources/views/landing.blade.php "$BACKUP_FILE"

# Remover o vídeo do final do arquivo (se existir)
echo "🗑️ Removendo vídeo do final do arquivo..."
sed -i '/{{-- Vídeo Demo do YouTube --}/,/iframe>/d' resources/views/landing.blade.php

# Encontrar a posição correta para adicionar o vídeo
# Vamos adicionar antes do último </div>@endif

echo "📝 Adicionando vídeo na posição correta..."
# Criar arquivo temporário com o código do vídeo
TEMP_FILE="/tmp/video_code.txt"
cat > "$TEMP_FILE" << 'VIDEOEOF'

{{-- Vídeo Demo do YouTube --}}
<div class="mb-6">
    <h2 class="text-xl font-semibold mb-4">Veja como funciona</h2>
    <div class="aspect-video bg-black rounded-lg overflow-hidden">
        <iframe
            width="560"
            height="315"
            src="https://www.youtube.com/embed/dQw4w9WgXQ"
            title="Demo Cobrança Auto"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
            class="w-full h-full"
        ></iframe>
    </div>
</div>
VIDEOEOF

# Adicionar código do vídeo antes do último </div>@endif
# Primeiro, vamos encontrar o último </div>@endif e adicionar antes dele
sed -i 's|</div>\n@endif|</div>\n@endif\n<!-- VIDEO_PLACEHOLDER -->|' resources/views/landing.blade.php

# Substituir o placeholder pelo código do vídeo
sed -i '/<!-- VIDEO_PLACEHOLDER -->/r /tmp/video_code.txt' resources/views/landing.blade.php
sed -i '/<!-- VIDEO_PLACEHOLDER -->/d' resources/views/landing.blade.php

# Limpar arquivo temporário
rm -f "$TEMP_FILE"

# Limpar cache
echo "🧹 Limpando cache do Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "✅ Vídeo ajustado com sucesso!"
echo "📦 Backup criado em: $BACKUP_FILE"
echo "🌐 Acesse: https://api.cobrancaauto.com.br"
