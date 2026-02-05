#!/bin/bash

# Script para adicionar vídeo demo à página inicial
# Executar na VPS

set -e

echo "🎬 Adicionando vídeo demo à página inicial..."

cd /var/www/cobranca-api

# Backup do arquivo
BACKUP_FILE="resources/views/landing.blade.php.backup.$(date +%Y%m%d_%H%M%S)"
echo "📦 Criando backup: $BACKUP_FILE"
cp resources/views/landing.blade.php "$BACKUP_FILE"

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

# Adicionar código do vídeo ao final do arquivo
echo "📝 Adicionando código do vídeo ao final do arquivo..."
cat "$TEMP_FILE" >> resources/views/landing.blade.php

# Limpar arquivo temporário
rm -f "$TEMP_FILE"

# Limpar cache
echo "🧹 Limpando cache do Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "✅ Vídeo adicionado com sucesso!"
echo "📦 Backup criado em: $BACKUP_FILE"
echo "🌐 Acesse: https://api.cobrancaauto.com.br"
