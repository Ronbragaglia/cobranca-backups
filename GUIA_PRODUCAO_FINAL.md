# 🚀 GUIA FINAL - PRODUÇÃO COBRANÇA API

## 🎉 STATUS: API 100% FUNCIONANDO ✅

### 📌 Informações de Produção

| Item | Valor |
|------|-------|
| **API ao Vivo** | https://api.cobrancaauto.com.br |
| **Dashboard Admin** | https://api.cobrancaauto.com.br/admin/saas/dashboard |
| **Login Admin** | admin@seucrm.com |
| **Senha Admin** | password |
| **Banco de Dados** | cobranca |
| **Usuário DB** | cobranca |
| **Senha DB** | Cobranca@2026 |
| **Tabelas** | 15 tabelas + dados demo |
| **Stack** | Node 20 + PHP 8.2 + MySQL 8 |
| **Status** | ✅ 100% FUNCIONANDO |

---

## 📦 Arquivos de Backup Criados

### Scripts de Backup/Restauração

| Arquivo | Descrição |
|---------|-----------|
| [`backup-completo.sh`](backup-completo.sh:1) | Script para backup local (desenvolvimento) |
| [`restaurar-backup.sh`](restaurar-backup.sh:1) | Script para restauração local |
| [`backup-producao.sh`](backup-producao.sh:1) | **Script para backup de PRODUÇÃO** |
| [`restaurar-producao.sh`](restaurar-producao.sh:1) | **Script para restauração de PRODUÇÃO** |

### Arquivos de Documentação

| Arquivo | Descrição |
|---------|-----------|
| [`cobranca_completo.sql`](cobranca_completo.sql:1) | Dump SQL completo (desenvolvimento) |
| [`README_BACKUP.md`](README_BACKUP.md:1) | Documentação detalhada do backup |
| [`INSTRUCOES_BACKUP_FINAL.md`](INSTRUCOES_BACKUP_FINAL.md:1) | Guia completo de backup local |
| [`GUIA_PRODUCAO_FINAL.md`](GUIA_PRODUCAO_FINAL.md:1) | **Este guia - Produção** |

---

## 🚀 COMANDOS PARA BACKUP DE PRODUÇÃO

### 1️⃣ Criar Backup de Produção

```bash
# Executar script de backup de produção
./backup-producao.sh
```

Este script vai:
- ✅ Fazer dump do banco de dados `cobranca`
- ✅ Copiar todas as migrations
- ✅ Copiar todos os seeders
- ✅ Copiar arquivos de configuração
- ✅ Criar arquivo `.tar.gz` compactado

**Arquivos gerados:**
- `backups/cobranca_producao_YYYYMMDD_HHMMSS.sql` (dump SQL)
- `backups/cobranca_producao_YYYYMMDD_HHMMSS.tar.gz` (backup completo)

### 2️⃣ Restaurar Backup de Produção

```bash
# Restaurar do arquivo SQL
./restaurar-producao.sh backups/cobranca_producao_YYYYMMDD_HHMMSS.sql

# OU restaurar do arquivo compactado
./restaurar-producao.sh backups/cobranca_producao_YYYYMMDD_HHMMSS.tar.gz
```

---

## 📤 ENVIAR BACKUP VIA GITHUB

### Criar Repositório e Fazer Upload

```bash
# Inicializar git (se ainda não tiver)
git init

# Adicionar arquivos de backup
git add backup-completo.sh
git add restaurar-backup.sh
git add backup-producao.sh
git add restaurar-producao.sh
git add cobranca_completo.sql
git add README_BACKUP.md
git add INSTRUCOES_BACKUP_FINAL.md
git add GUIA_PRODUCAO_FINAL.md

# Commit
git commit -m "Backup completo Cobrança API - Produção"

# Adicionar remote (substitua seu-usuario)
git remote add origin https://github.com/seu-usuario/cobranca-backup.git

# Push para GitHub
git branch -M main
git push -u origin main
```

### Clonar e Restaurar em Outro Servidor

```bash
# Clonar repositório
git clone https://github.com/seu-usuario/cobranca-backup.git
cd cobranca-backup

# Restaurar banco de dados
mysql -u cobranca -p cobranca < cobranca_completo.sql

# OU usar script de restauração
./restaurar-producao.sh cobranca_completo.sql
```

---

## 📦 ENVIAR BACKUP VIA ZIP

### Criar ZIP Completo

```bash
# Criar ZIP com todos os arquivos essenciais
zip -r cobranca_backup_producao.zip \
  backup-completo.sh \
  restaurar-backup.sh \
  backup-producao.sh \
  restaurar-producao.sh \
  cobranca_completo.sql \
  database/migrations/*.php \
  database/seeders/*.php \
  composer.json \
  .env.example \
  README.md \
  README_BACKUP.md \
  INSTRUCOES_BACKUP_FINAL.md \
  GUIA_PRODUCAO_FINAL.md
```

### Restaurar do ZIP

```bash
# Extrair ZIP
unzip cobranca_backup_producao.zip

# Restaurar banco de dados
mysql -u cobranca -p cobranca < cobranca_completo.sql

# OU usar script de restauração
./restaurar-producao.sh cobranca_completo.sql
```

---

## 🔐 Credenciais de Produção

### Acesso Admin

| Email | Senha | Permissão |
|-------|-------|-----------|
| admin@seucrm.com | password | Super Admin (Produção) |
| demo@seucrm.com | password | Admin Demo |
| admin@cobranca.com | 123456 | Admin (Desenvolvimento) |

### Banco de Dados

| Parâmetro | Valor |
|-----------|-------|
| Host | localhost |
| Database | cobranca |
| User | cobranca |
| Password | Cobranca@2026 |
| Charset | utf8mb4 |
| Collation | utf8mb4_unicode_ci |

---

## 📊 Estrutura do Banco de Dados

### Tabelas Principais (15 tabelas)

```
├── users                    # Usuários do sistema
├── tenants                  # Tenants multi-tenant
├── plans                    # Planos de assinatura
├── subscriptions            # Assinaturas dos tenants
├── cobrancas                # Cobranças/Boletos
├── cobranca_envios          # Histórico de envios
├── api_keys                 # Chaves de API
├── message_templates        # Templates de mensagens
├── audit_logs               # Logs de auditoria
├── beta_testers             # Beta testers
├── tenant_settings          # Configurações por tenant
├── cache                    # Cache do sistema
├── jobs                     # Filas de jobs
├── sessions                 # Sessões de usuário
└── personal_access_tokens   # Tokens de autenticação
```

---

## 🌐 Endpoints da API

### Status da API
```bash
curl -i https://api.cobrancaauto.com.br/api/status
```

### Login (Obter Token)
```bash
curl -i -X POST https://api.cobrancaauto.com.br/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@seucrm.com","password":"password"}'
```

### Listar Cobranças
```bash
curl -i https://api.cobrancaauto.com.br/api/cobrancas \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

### Criar Cobrança
```bash
curl -i -X POST https://api.cobrancaauto.com.br/api/cobrancas \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{
    "descricao": "Nova Cobrança",
    "valor": 150.00,
    "status": "pendente",
    "data_vencimento": "2026-02-10",
    "telefone": "(11) 99999-9999"
  }'
```

---

## 🧪 Testes Rápidos

### Testar Conexão com Banco de Dados
```bash
mysql -u cobranca -pCobranca@2026 -h localhost cobranca -e "SHOW TABLES;"
```

### Testar Status das Migrations
```bash
php artisan migrate:status
```

### Testar Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Testar Queue
```bash
php artisan queue:work --tries=1 --timeout=0
```

---

## 📋 Checklist de Backup/Restauração

### Backup de Produção
- [ ] MySQL rodando
- [ ] Credenciais corretas configuradas
- [ ] Script [`backup-producao.sh`](backup-producao.sh:1) executado
- [ ] Arquivo `.sql` gerado
- [ ] Arquivo `.tar.gz` gerado
- [ ] Backup armazenado em local seguro

### Restauração de Produção
- [ ] MySQL rodando
- [ ] Banco de dados `cobranca` criado
- [ ] Script [`restaurar-producao.sh`](restaurar-producao.sh:1) executado
- [ ] Tabelas restauradas
- [ ] Cache limpo
- [ ] Aplicação testada

---

## ⚠️ Notas Importantes

1. **Segurança:**
   - Altere a senha do admin após primeira instalação
   - Nunca exponha o arquivo `.env` publicamente
   - Mantenha backups em local seguro
   - Use HTTPS em produção

2. **Ambiente:**
   - `APP_ENV=production` em produção
   - `APP_DEBUG=false` em produção
   - Configure chaves reais do Stripe
   - Configure credenciais reais de email

3. **Backup Regular:**
   - Faça backup diário do banco de dados
   - Mantenha backups por 30 dias
   - Teste restauração regularmente
   - Armazene backups offsite

4. **Monitoramento:**
   - Monitore logs em `storage/logs/laravel.log`
   - Configure alertas de erro
   - Monitore uso de recursos
   - Verifique filas de jobs

---

## 🆘 Solução de Problemas

### Erro: Access denied for user 'cobranca'
```bash
# Verificar se o usuário existe
mysql -u root -p -e "SELECT user, host FROM mysql.user WHERE user='cobranca';"

# Criar usuário se não existir
mysql -u root -p -e "CREATE USER 'cobranca'@'localhost' IDENTIFIED BY 'Cobranca@2026';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON cobranca.* TO 'cobranca'@'localhost';"
mysql -u root -p -e "FLUSH PRIVILEGES;"
```

### Erro: Database not found
```bash
# Criar banco de dados
mysql -u cobranca -pCobranca@2026 -e "CREATE DATABASE IF NOT EXISTS cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Erro: Permission denied
```bash
# Dar permissão de execução
chmod +x backup-producao.sh
chmod +x restaurar-producao.sh
```

### Erro: mysqldump not found
```bash
# Instalar MySQL client
sudo apt update
sudo apt install mysql-client
```

---

## 📞 Suporte

### Documentação
- [`README.md`](README.md:1) - Documentação principal do projeto
- [`README_BACKUP.md`](README_BACKUP.md:1) - Documentação detalhada do backup
- [`INSTRUCOES_BACKUP_FINAL.md`](INSTRUCOES_BACKUP_FINAL.md:1) - Guia de backup local

### Links Úteis
- Documentação Laravel: https://laravel.com/docs
- Documentação MySQL: https://dev.mysql.com/doc/
- Stripe API: https://stripe.com/docs/api

---

## ✅ Resumo Rápido

### Backup de Produção
```bash
./backup-producao.sh
```

### Restaurar Produção
```bash
./restaurar-producao.sh backups/cobranca_producao_YYYYMMDD_HHMMSS.sql
```

### Acessar Dashboard
```
https://api.cobrancaauto.com.br/admin/saas/dashboard
```

### Login Admin
```
Email: admin@seucrm.com
Senha: password
```

---

## 🎉 SUCESSO!

**API Cobrança ao vivo e funcionando 100%! 🚀**

- ✅ MySQL configurado e funcionando
- ✅ 15 tabelas + dados demo
- ✅ Node 20 + PHP 8.2 + MySQL 8
- ✅ Laravel + Vite build OK
- ✅ Login + Dashboard funcionando
- ✅ HTTPS configurado
- ✅ Scripts de backup/restauração prontos

**Seu sistema de cobrança está pronto para faturar! 💰**
