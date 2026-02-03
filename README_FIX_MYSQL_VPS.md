# Fix Rápido - Problema de Conexão MySQL na VPS

## Problema
```
SQLSTATE[HY000] [2002] Connection refused
MYSQL NÃO ESTÁ RODANDO - tabela sessions não encontrada
```

## Causa
- MySQL não está instalado na VPS Ubuntu
- Serviço MySQL não está iniciado

## Solução Rápida

### Passo 1: Execute o script de correção
```bash
cd /home/admin/projects/cobranca-api
sudo bash fix-mysql-vps.sh
```

### Passo 2: Execute as migrações do Laravel
```bash
cd /var/www/cobranca-api
php artisan migrate:fresh --seed --force
```

## Comandos Manuais (Alternativa)

Se preferir executar manualmente:

```bash
# Atualizar o sistema
sudo apt update

# Instalar MySQL Server e PHP MySQL extension
sudo apt install -y mysql-server php8.1-mysql

# Iniciar o serviço MySQL
sudo systemctl start mysql

# Habilitar o serviço MySQL para iniciar no boot
sudo systemctl enable mysql

# Criar banco de dados cobranca
sudo mysql -u root << EOF
CREATE DATABASE IF NOT EXISTS cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
FLUSH PRIVILEGES;
EOF

# Navegar até o diretório do projeto
cd /var/www/cobranca-api

# Executar as migrações do Laravel
php artisan migrate:fresh --seed --force
```

## Verificação

### Verificar status do MySQL
```bash
sudo systemctl status mysql
```

### Verificar se o banco foi criado
```bash
sudo mysql -u root -e "SHOW DATABASES;"
```

### Testar conexão com o banco
```bash
sudo mysql -u root -e "USE cobranca; SHOW TABLES;"
```

## Comandos Úteis

```bash
# Ver status do MySQL
sudo systemctl status mysql

# Parar MySQL
sudo systemctl stop mysql

# Iniciar MySQL
sudo systemctl start mysql

# Reiniciar MySQL
sudo systemctl restart mysql

# Acessar MySQL via linha de comando
sudo mysql -u root

# Ver logs do MySQL
sudo tail -f /var/log/mysql/error.log
```

## Solução de Problemas

### Erro: "Connection refused"
- Verifique se o MySQL está rodando: `sudo systemctl status mysql`
- Se não estiver, inicie: `sudo systemctl start mysql`

### Erro: "Access denied for user"
- Verifique as credenciais no arquivo `.env`
- O usuário padrão do MySQL root não tem senha por padrão

### Erro: "Unknown database"
- Crie o banco de dados: `sudo mysql -u root -e "CREATE DATABASE cobranca;"`
- Verifique se foi criado: `sudo mysql -u root -e "SHOW DATABASES;"`

## Configuração do .env

Certifique-se de que o arquivo `.env` está configurado corretamente:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cobranca
DB_USERNAME=root
DB_PASSWORD=
```

**Nota:** Por padrão, o MySQL root no Ubuntu não tem senha configurada. Se você configurou uma senha, atualize o `DB_PASSWORD` no `.env`.
