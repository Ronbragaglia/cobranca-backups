#!/bin/bash

# Script para executar a adição de vídeo na VPS remotamente
# Executar localmente

set -e

VPS_IP="76.13.167.54"
VPS_USER="root"
SCRIPT_NAME="adicionar-video-final.sh"
LOCAL_SCRIPT_PATH="scripts/$SCRIPT_NAME"
REMOTE_SCRIPT_PATH="/tmp/$SCRIPT_NAME"

echo "🚀 Enviando script para adicionar vídeo à VPS..."

# Enviar script para a VPS
echo "📤 Enviando script..."
scp "$LOCAL_SCRIPT_PATH" "$VPS_USER@$VPS_IP:$REMOTE_SCRIPT_PATH"

echo "🔧 Executando script na VPS..."
ssh "$VPS_USER@$VPS_IP" "chmod +x $REMOTE_SCRIPT_PATH && $REMOTE_SCRIPT_PATH"

echo "🧹 Limpando script temporário..."
ssh "$VPS_USER@$VPS_IP" "rm -f $REMOTE_SCRIPT_PATH"

echo "✅ Vídeo adicionado com sucesso!"
echo "🌐 Acesse: https://api.cobrancaauto.com.br"
