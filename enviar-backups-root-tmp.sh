#!/bin/bash
# ENVIAR_BACKUPS_PRODUCAO_ROOT_TMP.sh
IP_SERVIDOR="76.13.167.54"

echo "🚀 Enviando 11 backups pro servidor produção (destino: /root/tmp/)..."
cd /home/admin/projects/cobranca-api

# Primeiro criar diretório /root/tmp/ no servidor remoto
echo "📁 Criando diretório /root/tmp/ no servidor remoto..."
ssh root@$IP_SERVIDOR "mkdir -p /root/tmp && chmod 777 /root/tmp"

# Enviar arquivos
echo "📤 Enviando arquivos..."
scp backup-completo.sh root@$IP_SERVIDOR:/root/tmp/
scp restaurar-backup.sh root@$IP_SERVIDOR:/root/tmp/
scp backup-producao.sh root@$IP_SERVIDOR:/root/tmp/
scp restaurar-producao.sh root@$IP_SERVIDOR:/root/tmp/
scp cobranca_completo.sql root@$IP_SERVIDOR:/root/tmp/
scp README_BACKUP.md root@$IP_SERVIDOR:/root/tmp/
scp INSTRUCOES_BACKUP_FINAL.md root@$IP_SERVIDOR:/root/tmp/
scp GUIA_PRODUCAO_FINAL.md root@$IP_SERVIDOR:/root/tmp/
scp copiar-backups-root.sh root@$IP_SERVIDOR:/root/tmp/
scp INSTRUCOES_COPIAR_ROOT.md root@$IP_SERVIDOR:/root/tmp/
scp RELATORIO_TESTES_FINAL.md root@$IP_SERVIDOR:/root/tmp/

echo "✅ VERIFICA no servidor:"
echo "ssh root@$IP_SERVIDOR 'ls -lh /root/tmp/'"
