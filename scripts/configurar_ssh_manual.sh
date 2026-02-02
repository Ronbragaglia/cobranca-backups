#!/bin/bash

# =============================================================================
# CONFIGURAR CHAVE SSH NA VPS (MANUAL)
# VPS: 76.13.167.54 (Ubuntu 22.04, root)
# Senha SSH: 1Qaz2wsx@2026
# =============================================================================

SSH_PUB_KEY=$(cat ~/.ssh/cobranca_deploy.pub)

echo "🔑 Configurando chave SSH na VPS..."
echo "Chave pública: $SSH_PUB_KEY"
echo ""
echo "Execute este comando no seu terminal e digite a senha quando solicitado:"
echo ""
echo "ssh root@76.13.167.54 'mkdir -p /root/.ssh && echo \"$SSH_PUB_KEY\" >> /root/.ssh/authorized_keys && chmod 700 /root/.ssh && chmod 600 /root/.ssh/authorized_keys && systemctl restart ssh && echo \"✓ Chave SSH configurada!\"'"
echo ""
echo "Depois, teste:"
echo "ssh -i ~/.ssh/cobranca_deploy root@76.13.167.54 'echo \"✓ SSH sem senha funcionando!\"'"
