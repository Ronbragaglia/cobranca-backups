# Fix Rápido - Problema de Conexão MySQL

## Problema
```
SQLSTATE[HY000] [2002] Connection refused
MYSQL NÃO ESTÁ RODANDO - tabela sessions não encontrada
```

## Causa
- MySQL não está instalado no sistema WSL2
- Docker não está configurado para WSL2
- PHP não está instalado

## Solução Rápida

### Passo 1: Configure o Docker Desktop
1. Abra o Docker Desktop no Windows
2. Vá em **Settings** → **Resources** → **WSL Integration**
3. Ative a integração WSL2 para sua distribuição Debian
4. Clique em "Apply & Restart"
5. Feche e reabra o terminal WSL2

### Passo 2: Execute o script de correção
```bash
cd /home/admin/projects/cobranca-api
bash fix-mysql-wsl2.sh
```

### Passo 3: Atualize o .env.local
Edite o arquivo `.env.local`:
```bash
nano .env.local
```

Alterar para:
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cobranca
DB_USERNAME=root
DB_PASSWORD=root
```

### Passo 4: Execute as migrações
```bash
php artisan migrate --force
```

## Verificação
```bash
# Verificar se o MySQL está rodando
docker ps | grep mysql

# Testar conexão
docker-compose -f docker-compose.mysql.yml exec -T mysql mysql -uroot -proot -e "SHOW DATABASES;"
```

## Acessar phpMyAdmin
- URL: http://localhost:8080
- Usuário: root
- Senha: root

## Comandos Úteis
```bash
# Ver logs do MySQL
docker-compose -f docker-compose.mysql.yml logs mysql

# Parar MySQL
docker-compose -f docker-compose.mysql.yml down

# Reiniciar MySQL
docker-compose -f docker-compose.mysql.yml restart

# Acessar MySQL via linha de comando
docker-compose -f docker-compose.mysql.yml exec mysql mysql -uroot -proot
```

## Documentação Completa
Para mais detalhes, consulte: [`SOLUCAO_MYSQL_WSL2.md`](SOLUCAO_MYSQL_WSL2.md)
