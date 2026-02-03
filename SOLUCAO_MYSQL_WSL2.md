# Solução Completa: Problema de Conexão MySQL no WSL2

## Diagnóstico Atual

**Ambiente Identificado:**
- Sistema: Debian 13 (trixie) no WSL2
- MySQL: Não instalado
- Docker: Não configurado no WSL2
- PHP: Não instalado
- Erro: `SQLSTATE[HY000] [2002] Connection refused`

## Opções de Solução

### Opção 1: Usar MySQL via Docker (RECOMENDADO)

Esta é a melhor opção pois mantém o ambiente isolado e configurado corretamente.

#### Passo 1: Configurar Docker Desktop para WSL2

1. Abra o Docker Desktop no Windows
2. Vá em **Settings** → **Resources** → **WSL Integration**
3. Ative a integração WSL2 para sua distribuição Debian
4. Clique em "Apply & Restart"
5. Feche e reabra o terminal WSL2

#### Passo 2: Iniciar MySQL via Docker

```bash
# Navegue até o diretório do projeto
cd /home/admin/projects/cobranca-api

# Verifique se o docker-compose.mysql.yml existe
ls -la docker-compose.mysql.yml

# Inicie o MySQL
docker-compose -f docker-compose.mysql.yml up -d

# Verifique se o container está rodando
docker ps | grep mysql
```

#### Passo 3: Configurar o arquivo .env

Edite o arquivo `.env` ou `.env.local`:

```bash
nano .env.local
```

Alterar as configurações de banco de dados para:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cobranca
DB_USERNAME=cobranca
DB_PASSWORD=sua_senha_aqui
```

Ou se quiser usar root:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cobranca
DB_USERNAME=root
DB_PASSWORD=root
```

#### Passo 4: Executar as migrações

```bash
# Aguarde o MySQL estar completamente pronto (cerca de 10-15 segundos)
sleep 15

# Execute as migrações
docker-compose -f docker-compose.mysql.yml exec -T mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Verifique se o banco foi criado
docker-compose -f docker-compose.mysql.yml exec -T mysql mysql -uroot -proot -e "SHOW DATABASES;"
```

#### Passo 5: Testar a conexão

```bash
# Teste a conexão com o MySQL
docker-compose -f docker-compose.mysql.yml exec -T mysql mysql -uroot -proot -e "SELECT 'Conexão bem-sucedida!' AS status;"
```

---

### Opção 2: Instalar MySQL nativo no WSL2

Se preferir instalar o MySQL diretamente no WSL2:

#### Passo 1: Atualizar o sistema

```bash
sudo apt update
sudo apt upgrade -y
```

#### Passo 2: Instalar MySQL Server

```bash
sudo apt install mysql-server -y
```

#### Passo 3: Iniciar o serviço MySQL

```bash
# Iniciar o MySQL
sudo service mysql start

# Verificar status
sudo service mysql status
```

#### Passo 4: Configurar o MySQL

```bash
# Acessar o MySQL
sudo mysql

# No prompt do MySQL, execute:
CREATE DATABASE cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'root'@'localhost' IDENTIFIED BY 'root';
GRANT ALL PRIVILEGES ON cobranca.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Passo 5: Configurar o arquivo .env

Edite o arquivo `.env` ou `.env.local`:

```bash
nano .env.local
```

Alterar as configurações de banco de dados para:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cobranca
DB_USERNAME=root
DB_PASSWORD=root
```

---

### Opção 3: Usar SQLite (Alternativa Simples)

Se você quer uma solução mais simples sem configurar MySQL:

#### Passo 1: Criar arquivo de banco SQLite

```bash
touch database/database.sqlite
```

#### Passo 2: Configurar o arquivo .env

Edite o arquivo `.env` ou `.env.local`:

```bash
nano .env.local
```

Alterar as configurações de banco de dados para:

```env
DB_CONNECTION=sqlite
# DB_HOST=mysql
# DB_PORT=3306
# DB_DATABASE=cobranca
# DB_USERNAME=cobranca
# DB_PASSWORD=__SET_IN_SERVER_ONLY__
```

#### Passo 3: Executar as migrações

```bash
php artisan migrate
```

---

## Solução Rápida (Recomendada)

Para resolver o problema **AGORA** com o menor esforço:

### 1. Configure o Docker Desktop para WSL2

Abra o Docker Desktop no Windows e ative a integração WSL2.

### 2. Execute estes comandos no terminal WSL2:

```bash
cd /home/admin/projects/cobranca-api

# Inicie o MySQL
docker-compose -f docker-compose.mysql.yml up -d

# Aguarde 15 segundos para o MySQL iniciar
sleep 15

# Crie o banco de dados
docker-compose -f docker-compose.mysql.yml exec -T mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Verifique se está funcionando
docker-compose -f docker-compose.mysql.yml exec -T mysql mysql -uroot -proot -e "SHOW DATABASES;"
```

### 3. Atualize o .env.local:

```bash
cat > .env.local << 'EOF'
APP_NAME="CobrançaAuto"
APP_ENV=local
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cobranca
DB_USERNAME=root
DB_PASSWORD=root

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
EOF
```

### 4. Teste a conexão:

```bash
# Verifique se o container está rodando
docker ps | grep mysql

# Teste a conexão
docker-compose -f docker-compose.mysql.yml exec -T mysql mysql -uroot -proot -e "SELECT 'MySQL está funcionando!' AS status;"
```

---

## Verificação Final

Depois de configurar, execute:

```bash
# Verifique se o MySQL está acessível
docker-compose -f docker-compose.mysql.yml exec -T mysql mysql -uroot -proot -e "SHOW DATABASES;"

# Deve mostrar:
# +--------------------+
# | Database           |
# +--------------------+
# | cobranca           |
# | information_schema |
# | mysql              |
# | performance_schema |
# | sys                |
# +--------------------+
```

---

## Comandos Úteis

### Verificar logs do MySQL
```bash
docker-compose -f docker-compose.mysql.yml logs mysql
```

### Parar o MySQL
```bash
docker-compose -f docker-compose.mysql.yml down
```

### Reiniciar o MySQL
```bash
docker-compose -f docker-compose.mysql.yml restart
```

### Acessar o MySQL via linha de comando
```bash
docker-compose -f docker-compose.mysql.yml exec mysql mysql -uroot -proot
```

### Acessar phpMyAdmin
Abra no navegador: `http://localhost:8080`
- Usuário: root
- Senha: root

---

## Solução de Problemas

### Erro: "Connection refused"
- Verifique se o container MySQL está rodando: `docker ps | grep mysql`
- Verifique se o Docker Desktop está rodando no Windows
- Verifique se a integração WSL2 está ativada no Docker Desktop

### Erro: "Access denied for user"
- Verifique as credenciais no arquivo .env
- Verifique se o usuário foi criado corretamente no MySQL

### Erro: "Unknown database"
- Crie o banco de dados: `docker-compose -f docker-compose.mysql.yml exec -T mysql mysql -uroot -proot -e "CREATE DATABASE cobranca;"`
