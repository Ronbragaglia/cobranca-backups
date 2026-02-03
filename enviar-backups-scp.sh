#!/bin/bash

# ============================================
# SCRIPT PARA ENVIAR BACKUPS VIA SCP
# ============================================

echo "📦 Enviando arquivos de backup via SCP..."
echo ""

# Servidor remoto
REMOTE_SERVER="root@76.13.167.54"
REMOTE_DIR="/tmp/"

# Arquivos para enviar
FILES=(
  "backup-completo.sh"
  "restaurar-backup.sh"
  "backup-producao.sh"
  "restaurar-producao.sh"
  "cobranca_completo.sql"
  "README_BACKUP.md"
  "INSTRUCOES_BACKUP_FINAL.md"
  "GUIA_PRODUCAO_FINAL.md"
  "INSTRUCOES_COPIAR_ROOT.md"
  "RELATORIO_TESTES_FINAL.md"
)

# Enviar cada arquivo
for file in "${FILES[@]}"; do
  echo "📤 Enviando $file..."
  scp "$file" "$REMOTE_SERVER:$REMOTE_DIR"
  
  if [ $? -eq 0 ]; then
    echo "✅ $file enviado com sucesso"
  else
    echo "❌ Erro ao enviar $file"
  fi
done

echo ""
echo "🎉 Todos os arquivos foram enviados para $REMOTE_SERVER:$REMOTE_DIR"
echo ""
echo "Para verificar no servidor remoto:"
echo "  ssh $REMOTE_SERVER 'ls -lh $REMOTE_DIR'"
