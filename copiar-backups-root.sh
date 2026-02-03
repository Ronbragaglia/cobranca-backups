#!/bin/bash

# ============================================
# SCRIPT PARA COPIAR BACKUPS PARA /root/backups/
# ============================================

echo "📦 Copiando arquivos de backup para /root/backups/..."

# Criar diretório /root/backups se não existir
sudo mkdir -p /root/backups

# Copiar os 8 arquivos de backup
sudo cp backup-completo.sh /root/backups/
sudo cp restaurar-backup.sh /root/backups/
sudo cp backup-producao.sh /root/backups/
sudo cp restaurar-producao.sh /root/backups/
sudo cp cobranca_completo.sql /root/backups/
sudo cp README_BACKUP.md /root/backups/
sudo cp INSTRUCOES_BACKUP_FINAL.md /root/backups/
sudo cp GUIA_PRODUCAO_FINAL.md /root/backups/

# Dar permissão de execução aos scripts
sudo chmod +x /root/backups/backup-completo.sh
sudo chmod +x /root/backups/restaurar-backup.sh
sudo chmod +x /root/backups/backup-producao.sh
sudo chmod +x /root/backups/restaurar-producao.sh

# Listar arquivos copiados
echo ""
echo "✅ Arquivos copiados com sucesso para /root/backups/:"
echo ""
sudo ls -lh /root/backups/
echo ""
echo "🎉 Pronto! Todos os backups estão em /root/backups/"
