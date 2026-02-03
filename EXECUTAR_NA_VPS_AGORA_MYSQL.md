# EXECUTAR NA VPS AGORA - Resolver Problema MySQL

## Situação Atual

**Ambiente:** WSL2 (não VPS real)
**Problema:** MySQL não está instalado, Docker não disponível, PHP não instalado
**Solução:** Você precisa executar estes comandos na VPS real (srv1298946 - IP 76.13.167.54)

## Conecte-se à VPS

```bash
ssh root@76.13.167.54
# ou
ssh seu_usuario@76.13.167.54
```

## Execute os seguintes comandos na VPS

### Passo 1: Atualizar o sistema
```bash
apt update
```

### Passo 2: Instalar MySQL Server e PHP MySQL extension
```bash
apt install -y mysql-server php8.1-mysql
```

### Passo 3: Iniciar o serviço MySQL
```bash
systemctl start mysql
```

### Passo 4: Habilitar o MySQL para iniciar no boot
```bash
systemctl enable mysql
```

### Passo 5: Verificar status do MySQL
```bash
systemctl status mysql
```

### Passo 6: Criar banco de dados cobranca
```bash
mysql -u root << EOF
CREATE DATABASE IF NOT EXISTS cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
FLUSH PRIVILEGES;
EOF
```

### Passo 7: Verificar se o banco foi criado
```bash
mysql -u root -e "SHOW DATABASES;"
```

### Passo 8: Navegar até o diretório do projeto
```bash
cd /var/www/cobranca-api
```

### Passo 9: Verificar se o arquivo .env existe
```bash
ls -la .env
```

### Passo 10: Editar o arquivo .env (se necessário)
```bash
nano .env
```

Certifique-se de que as configurações de banco de dados estão assim:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cobranca
DB_USERNAME=root
DB_PASSWORD=
```

**Nota:** Por padrão, o MySQL root no Ubuntu não tem senha configurada. Se você configurou uma senha, atualize o `DB_PASSWORD` no `.env`.

### Passo 11: Executar as migrações do Laravel
```bash
php artisan migrate:fresh --seed --force
```

### Passo 12: Verificar se as tabelas foram criadas
```bash
mysql -u root -e "USE cobranca; SHOW TABLES;"
```

## Verificação Final

### Testar a conexão com o banco de dados
```bash
mysql -u root -e "SELECT 'Conexão com MySQL bem-sucedida!' AS status;"
```

### Verificar se o Laravel consegue conectar
```bash
php artisan tinker
```

No prompt do tinker, execute:
```php
DB::connection()->getPdo();
```

Se não der erro, a conexão está funcionando. Digite `exit` para sair.

## Comandos Úteis

```bash
# Ver status do MySQL
systemctl status mysql

# Parar MySQL
systemctl stop mysql

# Iniciar MySQL
systemctl start mysql

# Reiniciar MySQL
systemctl restart mysql

# Acessar MySQL via linha de comando
mysql -u root

# Ver logs do MySQL
tail -f /var/log/mysql/error.log

# Ver tabelas do banco cobranca
mysql -u root -e "USE cobranca; SHOW TABLES;"

# Ver estrutura de uma tabela específica
mysql -u root -e "USE cobranca; DESCRIBE users;"
```

## Solução de Problemas

### Erro: "Connection refused"
- Verifique se o MySQL está rodando: `systemctl status mysql`
- Se não estiver, inicie: `systemctl start mysql`

### Erro: "Access denied for user"
- Verifique as credenciais no arquivo `.env`
- O usuário padrão do MySQL root não tem senha por padrão

### Erro: "Unknown database"
- Crie o banco de dados: `mysql -u root -e "CREATE DATABASE cobranca;"`
- Verifique se foi criado: `mysql -u root -e "SHOW DATABASES;"`

### Erro: "SQLSTATE[HY000] [2002] Connection refused"
- Verifique se o MySQL está rodando: `systemctl status mysql`
- Verifique as configurações do `.env` (DB_HOST deve ser 127.0.0.1 ou localhost)
- Verifique se o banco de dados existe: `mysql -u root -e "SHOW DATABASES;"`

## Resumo dos Comandos (Copie e Cole)

```bash
# Atualizar o sistema
apt update

# Instalar MySQL Server e PHP MySQL extension
apt install -y mysql-server php8.1-mysql

# Iniciar o serviço MySQL
systemctl start mysql

# Habilitar o MySQL para iniciar no boot
systemctl enable mysql

# Verificar status do MySQL
systemctl status mysql

# Criar banco de dados cobranca
mysql -u root << EOF
CREATE DATABASE IF NOT EXISTS cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
FLUSH PRIVILEGES;
EOF

# Verificar se o banco foi criado
mysql -u root -e "SHOW DATABASES;"

# Navegar até o diretório do projeto
cd /var/www/cobranca-api

# Executar as migrações do Laravel
php artisan migrate:fresh --seed --force

# Verificar se as tabelas foram criadas
mysql -u root -e "USE cobranca; SHOW TABLES;"
```

## Próximos Passos

Após executar todos os comandos acima, o problema de conexão MySQL deve estar resolvido. Você pode verificar:

1. Se o MySQL está rodando: `systemctl status mysql`
2. Se o banco de dados existe: `mysql -u root -e "SHOW DATABASES;"`
3. Se as tabelas foram criadas: `mysql -u root -e "USE cobranca; SHOW TABLES;"`
4. Se o Laravel consegue conectar: `php artisan tinker` → `DB::connection()->getPdo();`

Se tudo estiver funcionando, o erro `SQLSTATE[HY000] [2002] Connection refused` deve estar resolvido.
