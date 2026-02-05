#!/bin/bash
# Script para enviar o projeto cobranca-api para a VPS

echo "📤 Enviando projeto cobranca-api para a VPS..."
echo ""

# Enviar o arquivo compactado para a VPS
scp -i ~/.ssh/hostinger_vps cobranca-api-completo.tar.gz root@76.13.167.54:/root/

if [ $? -eq 0 ]; then
    echo "✅ Arquivo enviado com sucesso!"
    echo ""
    echo "🚀 Próximos passos:"
    echo "1. Conecte-se à VPS: ssh root@76.13.167.54"
    echo "2. Extraia o arquivo: cd /root && tar -xzf cobranca-api-completo.tar.gz"
    echo "3. Entre no diretório: cd cobranca-api"
    echo "4. Instale as dependências: composer install"
    echo "5. Configure o ambiente: cp .env.example .env"
    echo "6. Gere a chave: php artisan key:generate"
    echo "7. Execute as migrations: php artisan migrate"
else
    echo "❌ Erro ao enviar o arquivo!"
    echo "Verifique:"
    echo "- Se a chave SSH existe: ls -la ~/.ssh/hostinger_vps"
    echo "- Se a VPS está acessível: ping 76.13.167.54"
    echo "- Se o arquivo existe: ls -lh cobranca-api-completo.tar.gz"
fi
