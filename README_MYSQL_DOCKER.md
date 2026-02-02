# MySQL via Docker para Projeto Cobrança API

Este arquivo contém instruções para configurar e usar MySQL via Docker com o projeto Laravel Cobrança API.

## Arquivos Criados

- [`docker-compose.mysql.yml`](docker-compose.mysql.yml) - Configuração do Docker Compose com MySQL e phpMyAdmin
- [`.env.mysql`](.env.mysql) - Configurações de conexão com o banco MySQL

## Configuração do Banco de Dados

### Credenciais do MySQL

- **Host (fora do Docker):** `127.0.0.1` ou `localhost`
- **Host (dentro do Docker):** `mysql`
- **Porta:** `3306`
- **Banco de Dados:** `cobranca`
- **Usuário:** `cobranca`
- **Senha:** `__SET_IN_SERVER_ONLY__`
- **Root Password:** `__SET_IN_SERVER_ONLY__`

### phpMyAdmin

- **URL:** http://localhost:8080
- **Usuário:** `root`
- **Senha:** `__SET_IN_SERVER_ONLY__`

## Como Usar

### 1. Subir os containers MySQL

```bash
docker-compose -f docker-compose.mysql.yml up -d
```

### 2. Verificar se o MySQL está rodando

```bash
docker-compose -f docker-compose.mysql.yml ps
```

### 3. Verificar logs do MySQL

```bash
docker-compose -f docker-compose.mysql.yml logs mysql
```

### 4. Acessar o phpMyAdmin

Abra o navegador e acesse: http://localhost:8080

## Configurar o Projeto Laravel para usar MySQL

### Opção 1: Atualizar o .env existente

Edite o arquivo [`.env`](.env) e altere as configurações do banco de dados:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cobranca
DB_USERNAME=cobranca
DB_PASSWORD=__SET_IN_SERVER_ONLY__
```

### Opção 2: Copiar o .env.mysql

```bash
# Se você quer usar as configurações do .env.mysql
# Copie as linhas do DB_* do .env.mysql para o seu .env
```

## Rodar as Migrations

Depois de configurar o `.env`, rode as migrations:

```bash
# Se estiver usando Laravel Sail
./vendor/bin/sail artisan migrate

# Se estiver rodando PHP localmente
php artisan migrate
```

## Rodar os Seeders

```bash
# Se estiver usando Laravel Sail
./vendor/bin/sail artisan db:seed

# Se estiver rodando PHP localmente
php artisan db:seed
```

## Comandos Úteis

### Parar os containers

```bash
docker-compose -f docker-compose.mysql.yml down
```

### Parar e remover volumes (cuidado: apaga os dados!)

```bash
docker-compose -f docker-compose.mysql.yml down -v
```

### Acessar o MySQL via terminal

```bash
docker exec -it cobranca_mysql mysql -u cobranca -p
# Senha: cobranca
```

### Acessar o MySQL como root

```bash
docker exec -it cobranca_mysql mysql -u root -p
# Senha: root
```

### Fazer backup do banco

```bash
docker exec cobranca_mysql mysqldump -u root -proot cobranca > backup.sql
```

### Restaurar backup do banco

```bash
docker exec -i cobranca_mysql mysql -u root -proot cobranca < backup.sql
```

## Solução de Problemas

### O MySQL não está iniciando

Verifique os logs:
```bash
docker-compose -f docker-compose.mysql.yml logs mysql
```

### Não consigo conectar ao MySQL

Verifique se o container está rodando:
```bash
docker ps | grep cobranca_mysql
```

Teste a conexão:
```bash
docker exec -it cobranca_mysql mysql -u cobranca -p
```

### Erro de conexão do Laravel

Certifique-se de que o `.env` está configurado corretamente:
- `DB_HOST=127.0.0.1` (se rodando PHP fora do Docker)
- `DB_HOST=mysql` (se rodando PHP dentro do Docker)

### Porta 3306 já está em uso

Se você já tem outro MySQL rodando na porta 3306, altere a porta no [`docker-compose.mysql.yml`](docker-compose.mysql.yml):

```yaml
ports:
  - "3307:3306"  # Usa a porta 3307 no host
```

E atualize o `.env`:
```env
DB_PORT=3307
```

## Estrutura do Docker Compose

O arquivo [`docker-compose.mysql.yml`](docker-compose.mysql.yml) contém:

1. **mysql** - Servidor MySQL 8.0
   - Volume persistente para dados
   - Healthcheck para garantir que está pronto
   - Porta 3306 exposta para o host

2. **phpmyadmin** - Interface web para gerenciar o MySQL
   - Acessível em http://localhost:8080
   - Conecta automaticamente ao container MySQL

## Volumes

- `mysql_data` - Volume persistente para os dados do MySQL

## Networks

- `cobranca_network` - Rede bridge para comunicação entre containers

## Próximos Passos

1. Suba os containers: `docker-compose -f docker-compose.mysql.yml up -d`
2. Configure o `.env` do Laravel para usar MySQL
3. Rode as migrations: `php artisan migrate`
4. Rode os seeders: `php artisan db:seed`
5. Acesse o phpMyAdmin em http://localhost:8080 para visualizar os dados
