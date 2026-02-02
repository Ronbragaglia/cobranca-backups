# Ambiente de Desenvolvimento Docker - CobrançaAuto SaaS

Este documento descreve como configurar e usar o ambiente de desenvolvimento local do CobrançaAuto SaaS usando Docker.

## 📋 Pré-requisitos

Antes de começar, certifique-se de ter instalado:

- **Docker Desktop** (Windows/Mac) ou **Docker Engine** (Linux)
  - Download: https://docs.docker.com/get-docker/
- **Docker Compose** (já incluído no Docker Desktop)
- **Insomnia** (para testar as APIs)
  - Download: https://insomnia.rest/download

## 🚀 Iniciando o Ambiente

### Opção 1: Usando o script automatizado (Recomendado)

Execute o script que configura tudo automaticamente:

```bash
bash scripts/start-dev.sh
```

Este script irá:
1. Verificar se Docker e Docker Compose estão instalados
2. Criar o arquivo `.env.local` se não existir
3. Configurar o ambiente Laravel
4. Iniciar todos os containers Docker
5. Aguardar o MySQL estar pronto
6. Instalar dependências PHP e Node
7. Executar migrations e seeders
8. Limpar cache e configurar permissões

### Opção 2: Manualmente

Se preferir configurar manualmente, siga estes passos:

#### 1. Configurar o arquivo .env

```bash
# Copiar o arquivo de exemplo
cp .env.local .env

# Se necessário, gerar a APP_KEY
php artisan key:generate
```

#### 2. Iniciar os containers

```bash
# Iniciar todos os serviços
docker-compose -f docker-compose.dev.yml up -d

# Verificar se os containers estão rodando
docker-compose -f docker-compose.dev.yml ps
```

#### 3. Aguardar o MySQL iniciar

```bash
# Verificar logs do MySQL
docker-compose -f docker-compose.dev.yml logs -f mysql

# Ou aguardar alguns segundos
sleep 15
```

#### 4. Executar migrations e seeders

```bash
# Executar migrations
docker-compose -f docker-compose.dev.yml exec app php artisan migrate --force

# Executar seeders
docker-compose -f docker-compose.dev.yml exec app php artisan db:seed --force
```

#### 5. Configurar permissões

```bash
docker-compose -f docker-compose.dev.yml exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose -f docker-compose.dev.yml exec app chmod -R 775 storage bootstrap/cache
```

## 🌐 Acessando os Serviços

Após iniciar o ambiente, você pode acessar:

| Serviço | URL | Descrição |
|---------|-----|-----------|
| **Aplicação** | http://localhost:8000 | Aplicação Laravel |
| **API** | http://localhost:8000/api | Endpoints da API REST |
| **phpMyAdmin** | http://localhost:8080 | Interface web do MySQL |

### Credenciais phpMyAdmin

- **Usuário:** `root`
- **Senha:** `root`

### Credenciais MySQL

- **Host:** `localhost:3306`
- **Banco de Dados:** `cobranca`
- **Usuário:** `cobranca`
- **Senha:** `cobranca`

## 📊 Estrutura dos Containers

O ambiente Docker consiste em 6 containers:

1. **app** - Servidor PHP-FPM com Laravel
2. **web** - Servidor Nginx (proxy reverso)
3. **mysql** - Banco de dados MySQL 8.0
4. **phpmyadmin** - Interface web para gerenciar o MySQL
5. **queue** - Worker para processamento de filas Laravel
6. **scheduler** - Scheduler para tarefas cron Laravel

## 🧪 Testando as APIs com Insomnia

### Importando a Coleção

1. Abra o Insomnia
2. Clique em **Application** → **Import/Export**
3. Selecione **Import From** → **File**
4. Escolha o arquivo `docs/insomnia-collection.json`
5. Clique em **Import**

### Configurando o Token

1. Execute a requisição **Login** para obter o token
2. Copie o token retornado
3. Vá em **Application** → **Environments**
4. Cole o token na variável `token`
5. Agora todas as requisições autenticadas usarão este token automaticamente

### Endpoints Disponíveis

#### Autenticação

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/login` | Autenticar usuário |
| POST | `/api/logout` | Desconectar usuário |
| GET | `/api/user` | Obter usuário autenticado |

#### Cobranças

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/cobrancas` | Listar todas as cobranças |
| POST | `/api/cobrancas` | Criar nova cobrança |
| GET | `/api/cobrancas/{id}` | Visualizar cobrança |
| PUT | `/api/cobrancas/{id}` | Atualizar cobrança |
| DELETE | `/api/cobrancas/{id}` | Deletar cobrança |

#### Outros

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/status` | Verificar status da API |

### Exemplo de Requisição

#### Login

```json
POST http://localhost:8000/api/login

{
  "email": "admin@cobrancaauto.com.br",
  "password": "password"
}
```

Resposta:
```json
{
  "token": "1|abc123xyz..."
}
```

#### Criar Cobrança

```json
POST http://localhost:8000/api/cobrancas
Authorization: Bearer {token}

{
  "descricao": "Pagamento Mensal",
  "valor": 100.00,
  "status": "pendente",
  "data_vencimento": "2026-02-15",
  "telefone": "(11) 99999-9999"
}
```

Resposta:
```json
{
  "id": 1,
  "descricao": "Pagamento Mensal",
  "valor": 100.00,
  "status": "pendente",
  "data_vencimento": "2026-02-15",
  "telefone": "(11) 99999-9999",
  "created_at": "2026-01-30T23:00:00.000000Z",
  "updated_at": "2026-01-30T23:00:00.000000Z"
}
```

## 🛠️ Comandos Úteis

### Gerenciar Containers

```bash
# Ver status dos containers
docker-compose -f docker-compose.dev.yml ps

# Ver logs de todos os containers
docker-compose -f docker-compose.dev.yml logs -f

# Ver logs de um container específico
docker-compose -f docker-compose.dev.yml logs -f app
docker-compose -f docker-compose.dev.yml logs -f mysql
docker-compose -f docker-compose.dev.yml logs -f web

# Parar todos os containers
docker-compose -f docker-compose.dev.yml stop

# Parar e remover containers
docker-compose -f docker-compose.dev.yml down

# Parar e remover containers e volumes (cuidado: apaga dados!)
docker-compose -f docker-compose.dev.yml down -v

# Reiniciar um container específico
docker-compose -f docker-compose.dev.yml restart app
```

### Executar Comandos Laravel

```bash
# Executar comando artisan
docker-compose -f docker-compose.dev.yml exec app php artisan migrate

# Executar seeder
docker-compose -f docker-compose.dev.yml exec app php artisan db:seed

# Limpar cache
docker-compose -f docker-compose.dev.yml exec app php artisan cache:clear

# Ver lista de rotas
docker-compose -f docker-compose.dev.yml exec app php artisan route:list

# Executar testes
docker-compose -f docker-compose.dev.yml exec app php artisan test

# Acessar o bash do container
docker-compose -f docker-compose.dev.yml exec app bash
```

### Gerenciar MySQL

```bash
# Acessar o MySQL via terminal
docker-compose -f docker-compose.dev.yml exec mysql mysql -u cobranca -p

# Fazer backup do banco
docker-compose -f docker-compose.dev.yml exec mysql mysqldump -u root -proot cobranca > backup.sql

# Restaurar backup
docker-compose -f docker-compose.dev.yml exec -T mysql mysql -u root -proot cobranca < backup.sql

# Acessar o MySQL como root
docker-compose -f docker-compose.dev.yml exec mysql mysql -u root -p
```

### Gerenciar Filas

```bash
# Ver status das filas
docker-compose -f docker-compose.dev.yml exec app php artisan queue:work --status

# Reiniciar o worker de filas
docker-compose -f docker-compose.dev.yml restart queue

# Limpar filas falhadas
docker-compose -f docker-compose.dev.yml exec app php artisan queue:flush
```

## 🔧 Solução de Problemas

### MySQL não está iniciando

```bash
# Ver logs do MySQL
docker-compose -f docker-compose.dev.yml logs mysql

# Reiniciar o MySQL
docker-compose -f docker-compose.dev.yml restart mysql
```

### Erro de conexão com o banco

```bash
# Verificar se o container MySQL está rodando
docker-compose -f docker-compose.dev.yml ps mysql

# Testar conexão com o MySQL
docker-compose -f docker-compose.dev.yml exec mysql mysqladmin ping -h localhost -u root -proot
```

### Erro de permissões

```bash
# Reconfigurar permissões
docker-compose -f docker-compose.dev.yml exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose -f docker-compose.dev.yml exec app chmod -R 775 storage bootstrap/cache
```

### Porta já está em uso

Se a porta 8000 ou 3306 já estiver em uso, edite o arquivo `docker-compose.dev.yml` e altere as portas:

```yaml
services:
  web:
    ports:
      - "8001:80"  # Usa porta 8001 em vez de 8000

  mysql:
    ports:
      - "3307:3306"  # Usa porta 3307 em vez de 3306
```

### Aplicação não acessível

```bash
# Ver logs do Nginx
docker-compose -f docker-compose.dev.yml logs web

# Ver logs do PHP-FPM
docker-compose -f docker-compose.dev.yml logs app

# Reiniciar os containers
docker-compose -f docker-compose.dev.yml restart web app
```

## 📝 Próximos Passos

1. **Explore as APIs:** Use o Insomnia para testar todos os endpoints
2. **Desenvolva novos recursos:** Adicione novas rotas e controllers
3. **Teste as funcionalidades:** Verifique se tudo está funcionando como esperado
4. **Documente as mudanças:** Mantenha a documentação atualizada

## 📚 Documentação Adicional

- [Documentação Laravel](https://laravel.com/docs)
- [Documentação Docker](https://docs.docker.com)
- [Documentação Insomnia](https://docs.insomnia.rest)
- [README do Projeto](../README.md)
- [MySQL via Docker](../README_MYSQL_DOCKER.md)

## 🆘 Suporte

Se encontrar algum problema:

1. Verifique os logs dos containers
2. Consulte a seção de solução de problemas acima
3. Entre em contato com a equipe de desenvolvimento

---

**Última atualização:** 30 de Janeiro de 2026
