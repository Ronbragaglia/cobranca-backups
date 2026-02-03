# 📦 Backup Completo - Cobrança API

## 📋 Conteúdo do Backup

Este backup contém todos os arquivos essenciais para restaurar o banco de dados e a estrutura do projeto Cobrança API:

### ✅ Arquivos Incluídos

1. **cobranca_completo.sql** - Dump completo do banco de dados MySQL
   - Todas as tabelas com estrutura completa
   - Dados de exemplo dos seeders (plans, tenants, users, cobrancas, etc.)
   - Índices e chaves estrangeiras configuradas

2. **database/migrations/** - Todas as migrações do Laravel
   - 24 arquivos de migração
   - Estrutura completa das tabelas

3. **database/seeders/** - Todos os seeders com dados reais
   - PlanSeeder.php (3 planos: Básico, Pro, Enterprise)
   - TenantSeeder.php (2 tenants: Principal e Demo)
   - AdminSeeder.php (usuário admin)
   - CobrancaSeeder.php (10 cobranças de exemplo)
   - MultiTenantSeeder.php (100 tenants para teste)
   - DatabaseSeeder.php (seeder principal)

4. **composer.json** - Dependências do projeto
   - PHP ^8.2
   - Laravel 12.0
   - Stripe PHP SDK
   - Laravel Sanctum

5. **.env.example** - Variáveis de ambiente
   - Configurações de banco de dados
   - Chaves do Stripe
   - Configurações de email

6. **README.md** - Documentação do projeto

---

## 🚀 Como Usar o Backup

### Opção 1: Usar Script Automático (Recomendado)

#### Criar Backup
```bash
# Dar permissão de execução
chmod +x backup-completo.sh

# Executar backup
./backup-completo.sh
```

O script criará um arquivo `.tar.gz` na pasta `backups/` com tudo organizado.

#### Restaurar Backup
```bash
# Dar permissão de execução
chmod +x restaurar-backup.sh

# Executar restauração
./restaurar-backup.sh backups/cobranca_backup_YYYYMMDD_HHMMSS.tar.gz
```

### Opção 2: Restauração Manual

#### 1. Restaurar Banco de Dados
```bash
# Criar banco de dados
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importar dump
mysql -u root -p cobranca < cobranca_completo.sql
```

#### 2. Configurar Projeto
```bash
# Instalar dependências
composer install

# Configurar .env
cp .env.example .env
php artisan key:generate

# Executar migrações e seeders
php artisan migrate --seed

# Build frontend
npm install
npm run build
```

---

## 📊 Estrutura do Banco de Dados

### Tabelas Principais

| Tabela | Descrição |
|--------|-----------|
| `users` | Usuários do sistema |
| `tenants` | Tenants multi-tenant |
| `plans` | Planos de assinatura |
| `subscriptions` | Assinaturas dos tenants |
| `cobrancas` | Cobranças/Boletos |
| `cobranca_envios` | Histórico de envios |
| `api_keys` | Chaves de API |
| `message_templates` | Templates de mensagens |
| `audit_logs` | Logs de auditoria |
| `beta_testers` | Beta testers |
| `tenant_settings` | Configurações por tenant |

### Tabelas do Laravel

| Tabela | Descrição |
|--------|-----------|
| `cache` | Cache do sistema |
| `jobs` | Filas de jobs |
| `sessions` | Sessões de usuário |
| `personal_access_tokens` | Tokens de autenticação |

---

## 🔐 Credenciais Padrão

### Usuários Admin

| Email | Senha | Tenant |
|-------|-------|--------|
| admin@seucrm.com | password | Principal |
| demo@seucrm.com | password | Demo |
| admin@cobranca.com | 123456 | Principal |

### Planos

| Plano | Preço | Mensagens | Instâncias |
|-------|-------|-----------|------------|
| Básico | R$ 97,00 | 500/mês | 1 |
| Pro | R$ 297,00 | 5.000/mês | 3 |
| Enterprise | R$ 997,00 | Ilimitadas | 10 |

---

## 📝 Comandos Úteis

### Backup via mysqldump
```bash
mysqldump -u root -p cobranca > cobranca_completo.sql
```

### Backup apenas estrutura
```bash
mysqldump -u root -p --no-data cobranca > estrutura.sql
```

### Backup apenas dados
```bash
mysqldump -u root -p --no-create-info cobranca > dados.sql
```

### Verificar status das migrações
```bash
php artisan migrate:status
```

### Rodar seeders específicos
```bash
# Apenas plans
php artisan db:seed --class=PlanSeeder

# Apenas tenants
php artisan db:seed --class=TenantSeeder

# Apenas cobranças
php artisan db:seed --class=CobrancaSeeder

# Todos
php artisan db:seed
```

---

## 🌐 Acessos Locais

### Multi-Tenant Setup

Para testar o tenant demo localmente:

1. Editar `/etc/hosts` (Linux/Mac) ou `C:\Windows\System32\drivers\etc\hosts` (Windows):
   ```
   127.0.0.1 demo.localhost
   ```

2. Acessar no navegador:
   - Principal: http://localhost
   - Demo: http://demo.localhost

---

## 📦 Enviar via GitHub

### Criar repositório e fazer upload

```bash
# Inicializar git
git init

# Adicionar arquivos do backup
git add cobranca_completo.sql
git add backup-completo.sh
git add restaurar-backup.sh
git add README_BACKUP.md

# Commit
git commit -m "Backup completo do banco de dados Cobrança API"

# Adicionar remote
git remote add origin https://github.com/seu-usuario/cobranca-backup.git

# Push
git push -u origin main
```

### Clonar e restaurar

```bash
# Clonar repositório
git clone https://github.com/seu-usuario/cobranca-backup.git
cd cobranca-backup

# Restaurar
mysql -u root -p cobranca < cobranca_completo.sql
```

---

## 📤 Enviar via ZIP

### Criar ZIP manual

```bash
# Criar ZIP com todos os arquivos
zip -r cobranca_backup_completo.zip \
  cobranca_completo.sql \
  database/migrations/*.php \
  database/seeders/*.php \
  composer.json \
  .env.example \
  README.md \
  backup-completo.sh \
  restaurar-backup.sh \
  README_BACKUP.md
```

### Restaurar do ZIP

```bash
# Extrair
unzip cobranca_backup_completo.zip

# Restaurar banco de dados
mysql -u root -p cobranca < cobranca_completo.sql
```

---

## ⚠️ Notas Importantes

1. **Segurança**: O backup contém senhas hash, mas não exponha publicamente
2. **Ambiente**: Ajuste as configurações no `.env` para produção
3. **Chaves Stripe**: Configure suas chaves reais no `.env`
4. **Backup Regular**: Faça backups regulares do banco de dados em produção
5. **Teste**: Sempre teste a restauração em ambiente de desenvolvimento primeiro

---

## 🆘 Suporte

Se precisar de ajuda:

1. Verifique o arquivo `README.md` principal do projeto
2. Consulte a documentação do Laravel: https://laravel.com/docs
3. Verifique os logs em `storage/logs/laravel.log`

---

## 📄 Licença

Este backup faz parte do projeto Cobrança API e segue a mesma licença do projeto principal.
