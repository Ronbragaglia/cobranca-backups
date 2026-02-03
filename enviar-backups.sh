#!/bin/bash
# ENVIAR_BACKUPS_PRODUCAO.sh
IP_SERVIDOR="76.13.167.54"

echo "🚀 Enviando 11 backups pro servidor produção..."
cd /home/admin/projects/cobranca-api

scp backup-completo.sh root@$IP_SERVIDOR:/tmp/
scp restaurar-backup.sh root@$IP_SERVIDOR:/tmp/
scp backup-producao.sh root@$IP_SERVIDOR:/tmp/
scp restaurar-producao.sh root@$IP_SERVIDOR:/tmp/
scp cobranca_completo.sql root@$IP_SERVIDOR:/tmp/
scp README_BACKUP.md root@$IP_SERVIDOR:/tmp/
scp INSTRUCOES_BACKUP_FINAL.md root@$IP_SERVIDOR:/tmp/
scp GUIA_PRODUCAO_FINAL.md root@$IP_SERVIDOR:/tmp/
scp copiar-backups-root.sh root@$IP_SERVIDOR:/tmp/
scp INSTRUCOES_COPIAR_ROOT.md root@$IP_SERVIDOR:/tmp/
scp RELATORIO_TESTES_FINAL.md root@$IP_SERVIDOR:/tmp/

echo "✅ VERIFICA no servidor:"
echo "ssh root@$IP_SERVIDOR 'ls -lh /tmp/*.sh /tmp/*.sql /tmp/*.md'"
