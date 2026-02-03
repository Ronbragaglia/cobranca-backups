# 🎯 GUIA FINAL - BACKUP COMPLETO COBRANÇA API

## 📦 Arquivos Criados

Todos os arquivos foram criados com sucesso na raiz do projeto:

### ✅ Arquivos Principais

| Arquivo | Descrição |
|---------|-----------|
| [`cobranca_completo.sql`](cobranca_completo.sql:1) | Dump completo do banco de dados MySQL com estrutura e dados |
| [`backup-completo.sh`](backup-completo.sh:1) | Script para criar backup automatizado |
| [`restaurar-backup.sh`](restaurar-backup.sh:1) | Script para restaurar backup |
| [`README_BACKUP.md`](README_BACKUP.md:1) | Documentação completa do backup |

### 📁 Backup Compactado

| Arquivo | Descrição |
|---------|-----------|
| [`backups/cobranca_backup_20260203_060112.tar.gz`](backups/cobranca_backup_20260203_060112.tar.gz:1) | Backup completo compactado (16KB) |

---

## 🚀 COMANDOS PARA RODAR NO PROJETO

### 1️⃣ Restaurar Banco de Dados (MySQL Vazio)

```bash
# Criar banco de dados
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importar dump completo
mysql -u root -p cobranca < cobranca_completo.sql
```

### 2️⃣ Configurar Projeto Laravel

```bash
# Instalar dependências PHP
composer install

# Configurar arquivo .env
cp .env.example .env
php artisan key:generate

# Executar migrações (se necessário)
php artisan migrate

# Executar seeders para popular dados
php artisan db:seed

# Instalar dependências Node.js
npm install

# Compilar assets
npm run build
```

### 3️⃣ Subir Servidor de Desenvolvimento

```bash
# Opção 1: Usando Laravel Sail (Docker)
./vendor/bin/sail up -d

# Opção 2: Usando PHP nativo
php artisan serve

# Opção 3: Usando Docker Compose
docker-compose up -d
```

### 4️⃣ Verificar Status

```bash
# Verificar status das migrações
php artisan migrate:status

# Verificar rotas da API
php artisan route:list

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 📤 ENVIAR VIA GITHUB

### Criar Repositório e Fazer Upload

```bash
# Inicializar git (se ainda não tiver)
git init

# Adicionar arquivos do backup
git add cobranca_completo.sql
git add backup-completo.sh
git add restaurar-backup.sh
git add README_BACKUP.md
git add INSTRUCOES_BACKUP_FINAL.md

# Commit
git commit -m "Backup completo do banco de dados Cobrança API"

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
mysql -u root -p cobranca < cobranca_completo.sql

# Continuar com configuração do projeto (veja seção 2 acima)
```

---

## 📦 ENVIAR VIA ZIP

### Criar ZIP Manual

```bash
# Criar ZIP com todos os arquivos essenciais
zip -r cobranca_backup_completo.zip \
  cobranca_completo.sql \
  database/migrations/*.php \
  database/seeders/*.php \
  composer.json \
  .env.example \
  README.md \
  backup-completo.sh \
  restaurar-backup.sh \
  README_BACKUP.md \
  INSTRUCOES_BACKUP_FINAL.md
```

### Restaurar do ZIP

```bash
# Extrair ZIP
unzip cobranca_backup_completo.zip

# Restaurar banco de dados
mysql -u root -p cobranca < cobranca_completo.sql

# Continuar com configuração do projeto (veja seção 2 acima)
```

---

## 🔐 CREDENCIAIS PADRÃO

### Usuários Admin

| Email | Senha | Tenant | Subdomínio |
|-------|-------|--------|------------|
| admin@seucrm.com | password | Principal | principal |
| demo@seucrm.com | password | Demo | demo |
| admin@cobranca.com | 123456 | Principal | principal |

### Planos de Assinatura

| Plano | Preço | Mensagens/mês | Instâncias WhatsApp |
|-------|-------|---------------|---------------------|
| Básico | R$ 97,00 | 500 | 1 |
| Pro | R$ 297,00 | 5.000 | 3 |
| Enterprise | R$ 997,00 | Ilimitadas | 10 |

---

## 📊 ESTRUTURA DO BANCO DE DADOS

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

## 🌐 ACESSOS LOCAIS (Multi-Tenant)

### Configurar Hosts

**Linux/Mac:** Editar `/etc/hosts`
```bash
sudo nano /etc/hosts
```

**Windows:** Editar `C:\Windows\System32\drivers\etc\hosts`

Adicionar:
```
127.0.0.1 demo.localhost
```

### Acessar no Navegador

- **Principal:** http://localhost
- **Demo:** http://demo.localhost

---

## 🧪 TESTAR API

### Status da API
```bash
curl -i http://localhost/api/status
```

### Login (Obter Token)
```bash
curl -i -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@seucrm.com","password":"password"}'
```

### Criar Cobrança
```bash
curl -i -X POST http://localhost/api/cobrancas \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{"descricao":"Nova Cobrança","valor":150.00,"status":"pendente"}'
```

---

## 📋 CHECKLIST DE RESTAURAÇÃO

- [ ] MySQL instalado e rodando
- [ ] PHP 8.2+ instalado
- [ ] Composer instalado
- [ ] Node.js e npm instalados
- [ ] Banco de dados `cobranca` criado
- [ ] Dump SQL importado
- [ ] Dependências Composer instaladas
- [ ] Arquivo `.env` configurado
- [ ] Key gerada (`php artisan key:generate`)
- [ ] Migrations executadas
- [ ] Seeders executados
- [ ] Dependências npm instaladas
- [ ] Assets compilados
- [ ] Servidor rodando

---

## ⚠️ NOTAS IMPORTANTES

1. **Segurança:** Altere as senhas padrão após a primeira instalação
2. **Ambiente:** Configure `APP_ENV=production` em produção
3. **Chaves Stripe:** Configure suas chaves reais no `.env`
4. **Backup Regular:** Faça backups regulares em produção
5. **Teste:** Sempre teste em ambiente de desenvolvimento primeiro

---

## 🆘 SOLUÇÃO DE PROBLEMAS

### Erro: Database not found
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Erro: Permission denied
```bash
chmod +x backup-completo.sh
chmod +x restaurar-backup.sh
```

### Erro: Composer not found
```bash
# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Erro: npm not found
```bash
# Instalar Node.js e npm
sudo apt update
sudo apt install nodejs npm
```

---

## 📞 SUPORTE

Para mais informações:
- Consulte [`README.md`](README.md:1) - Documentação principal do projeto
- Consulte [`README_BACKUP.md`](README_BACKUP.md:1) - Documentação detalhada do backup
- Documentação Laravel: https://laravel.com/docs

---

## ✅ RESUMO RÁPIDO

```bash
# 1. Restaurar banco de dados
mysql -u root -p cobranca < cobranca_completo.sql

# 2. Instalar dependências
composer install
npm install

# 3. Configurar
cp .env.example .env
php artisan key:generate

# 4. Executar migrations e seeders
php artisan migrate --seed

# 5. Build
npm run build

# 6. Rodar
php artisan serve
```

**Pronto! Seu projeto Cobrança API está configurado e pronto para uso! 🎉**
