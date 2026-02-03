# 📦 COPIAR BACKUPS PARA /root/backups/

## ✅ Arquivos Prontos

Todos os 8 arquivos de backup foram criados na raiz do projeto `/home/admin/projects/cobranca-api/`:

| Arquivo | Tamanho |
|---------|---------|
| backup-completo.sh | 3.3KB |
| restaurar-backup.sh | 3.9KB |
| backup-producao.sh | 4.0KB |
| restaurar-producao.sh | 4.8KB |
| cobranca_completo.sql | 24KB |
| README_BACKUP.md | 6.3KB |
| INSTRUCOES_BACKUP_FINAL.md | 7.7KB |
| GUIA_PRODUCAO_FINAL.md | 9.7KB |

---

## 🚀 Opção 1: Executar Script Automático

Execute o script criado:

```bash
cd /home/admin/projects/cobranca-api
./copiar-backups-root.sh
```

Este script irá:
- ✅ Criar diretório `/root/backups/`
- ✅ Copiar os 8 arquivos
- ✅ Dar permissão de execução aos scripts
- ✅ Listar os arquivos copiados

---

## 📋 Opção 2: Copiar Manualmente

Execute os comandos abaixo:

```bash
# Criar diretório
sudo mkdir -p /root/backups

# Copiar arquivos
sudo cp backup-completo.sh /root/backups/
sudo cp restaurar-backup.sh /root/backups/
sudo cp backup-producao.sh /root/backups/
sudo cp restaurar-producao.sh /root/backups/
sudo cp cobranca_completo.sql /root/backups/
sudo cp README_BACKUP.md /root/backups/
sudo cp INSTRUCOES_BACKUP_FINAL.md /root/backups/
sudo cp GUIA_PRODUCAO_FINAL.md /root/backups/

# Dar permissão de execução
sudo chmod +x /root/backups/backup-completo.sh
sudo chmod +x /root/backups/restaurar-backup.sh
sudo chmod +x /root/backups/backup-producao.sh
sudo chmod +x /root/backups/restaurar-producao.sh

# Verificar arquivos
sudo ls -lh /root/backups/
```

---

## 📤 Opção 3: Via SCP (de outro servidor)

Se você estiver em outro servidor e quiser copiar os arquivos:

```bash
# Copiar todos os arquivos de backup
scp admin@seu-servidor:/home/admin/projects/cobranca-api/backup-completo.sh /root/backups/
scp admin@seu-servidor:/home/admin/projects/cobranca-api/restaurar-backup.sh /root/backups/
scp admin@seu-servidor:/home/admin/projects/cobranca-api/backup-producao.sh /root/backups/
scp admin@seu-servidor:/home/admin/projects/cobranca-api/restaurar-producao.sh /root/backups/
scp admin@seu-servidor:/home/admin/projects/cobranca-api/cobranca_completo.sql /root/backups/
scp admin@seu-servidor:/home/admin/projects/cobranca-api/README_BACKUP.md /root/backups/
scp admin@seu-servidor:/home/admin/projects/cobranca-api/INSTRUCOES_BACKUP_FINAL.md /root/backups/
scp admin@seu-servidor:/home/admin/projects/cobranca-api/GUIA_PRODUCAO_FINAL.md /root/backups/
```

---

## ✅ Verificação

Após copiar os arquivos, verifique:

```bash
sudo ls -lh /root/backups/
```

Deve mostrar os 8 arquivos listados acima.

---

## 🎉 Pronto!

Após copiar os arquivos para `/root/backups/`, você terá:

- ✅ Todos os scripts de backup e restauração
- ✅ Dump SQL completo do banco de dados
- ✅ Documentação completa
- ✅ Guias de produção e desenvolvimento

**Agora é faturar com cliente! 💰🚀**
