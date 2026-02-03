# 🎉 Deploy para VPS via GitHub - SUCESSO!

## ✅ Status Atual

O projeto foi enviado com sucesso para o GitHub!

```
To https://github.com/Ronbragaglia/cobranca-api.git
 * [new branch]      main -> main
branch 'main' set up to track 'origin/main'.
```

## 📦 Informações do Repositório

- **URL:** https://github.com/Ronbragaglia/cobranca-api.git
- **Branch:** main
- **Commit:** Deploy inicial CobrancaAuto VPS
- **Arquivos:** 263 arquivos (38,116 inserções)

## 🚀 Próximos Passos - Clonar na VPS

### 1. Acessar a VPS:
```bash
ssh root@76.13.167.54
```

### 2. Navegar até /root:
```bash
cd /root
```

### 3. Clonar o repositório:
```bash
# Usar HTTPS (mais estável)
git clone https://github.com/Ronbragaglia/cobranca-api.git

# OU usar SSH (se a chave estiver configurada)
git clone git@github.com:Ronbragaglia/cobranca-api.git
```

### 4. Navegar até o projeto:
```bash
cd cobranca-api
```

### 5. Verificar os arquivos:
```bash
ls -la
```

## 📦 Instalar Dependências

### Dependências do PHP:
```bash
composer install --no-dev --optimize-autoloader
```

### Dependências do Node.js:
```bash
npm install
npm run build
```

## 🔧 Configurar o Ambiente

### 1. Copiar arquivo de exemplo:
```bash
cp .env.example .env
```

### 2. Gerar chave da aplicação:
```bash
php artisan key:generate
```

### 3. Editar o arquivo .env:
```bash
nano .env
```

**Configurações importantes:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

# Configurações do Banco de Dados
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cobranca_api
DB_USERNAME=root
DB_PASSWORD=sua_senha_mysql

# Configurações do Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua_senha_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu-email@gmail.com

# Configurações do Evolution API
EVOLUTION_API_URL=https://seu-evolution-api.com
EVOLUTION_API_KEY=sua_api_key
EVOLUTION_API_INSTANCE=sua_instancia
```

## 🗄️ Executar Migrações

```bash
php artisan migrate --force
```

## 🔐 Configurar Permissões

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 🌐 Configurar o Servidor Web

### Opção 1: Usar Nginx
```bash
# Instalar Nginx (se não estiver instalado)
apt update
apt install nginx -y

# Configurar Nginx
nano /etc/nginx/sites-available/cobranca-api
```

**Configuração do Nginx:**
```nginx
server {
    listen 80;
    server_name seu-dominio.com www.seu-dominio.com;
    root /root/cobranca-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /index.php {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### Opção 2: Usar Apache
```bash
# Instalar Apache (se não estiver instalado)
apt update
apt install apache2 libapache2-mod-php -y

# Configurar Apache
nano /etc/apache2/sites-available/cobranca-api.conf
```

**Configuração do Apache:**
```apache
<VirtualHost *:80>
    ServerName seu-dominio.com
    ServerAdmin admin@seu-dominio.com
    DocumentRoot /root/cobranca-api/public

    <Directory /root/cobranca-api/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

### Ativar o site:
```bash
# Nginx
ln -s /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-enabled/
systemctl restart nginx

# Apache
a2ensite cobranca-api.conf
systemctl restart apache2
```

## 🔒️ Configurar SSL (Let's Encrypt)

```bash
# Instalar Certbot
apt update
apt install certbot python3-certbot-nginx -y

# Obter certificado SSL
certbot --nginx -d seu-dominio.com -d www.seu-dominio.com

# Renovar automaticamente
certbot renew --dry-run
```

## 🔄 Configurar Processos em Background

### Configurar Queue Worker:
```bash
# Instalar Supervisor
apt update
apt install supervisor -y

# Criar configuração
nano /etc/supervisor/conf.d/cobranca-api-worker.conf
```

**Configuração do Supervisor:**
```ini
[program:cobranca-api-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /root/cobranca-api/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=root
numprocs=1
redirect_stderr=true
stdout_logfile=/root/cobranca-api/storage/logs/worker.log
stopwaitsecs=3600
```

### Iniciar o Supervisor:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start cobranca-api-worker:*
```

### Configurar Scheduler:
```bash
# Adicionar ao crontab
crontab -e
```

**Adicionar esta linha:**
```cron
* * * * * php /root/cobranca-api/artisan schedule:run >> /root/cobranca-api/storage/logs/scheduler.log 2>&1
```

## 📊 Monitoramento

### Verificar logs:
```bash
# Logs da aplicação
tail -f /root/cobranca-api/storage/logs/laravel.log

# Logs do worker
tail -f /root/cobranca-api/storage/logs/worker.log

# Logs do scheduler
tail -f /root/cobranca-api/storage/logs/scheduler.log
```

### Verificar processos:
```bash
# Verificar se o worker está rodando
supervisorctl status

# Verificar processos PHP
ps aux | grep php
```

## 🔧 Solução de Problemas

### Erro de permissão:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Erro de banco de dados:
```bash
# Verificar se o MySQL está rodando
systemctl status mysql

# Verificar logs do MySQL
tail -f /var/log/mysql/error.log
```

### Erro de dependências:
```bash
# Limpar cache do Composer
composer clear-cache

# Reinstalar dependências
composer install --no-dev --optimize-autoloader
```

## 📝 Resumo

1. ✅ Projeto enviado para o GitHub
2. 🔄 Próximo: Clonar na VPS
3. 📦 Instalar dependências (composer, npm)
4. 🔧 Configurar ambiente (.env)
5. 🗄️ Executar migrações
6. 🔐 Configurar permissões
7. 🌐 Configurar servidor web (Nginx/Apache)
8. 🔒️ Configurar SSL
9. 🔄 Configurar processos em background (queue, scheduler)
10. 📊 Monitorar logs

## 🔗 Links Úteis

- **Repositório GitHub:** https://github.com/Ronbragaglia/cobranca-api
- **Documentação Laravel:** https://laravel.com/docs
- **Documentação Nginx:** https://nginx.org/en/docs/
- **Documentação Supervisor:** http://supervisord.org/

---

**Data:** 2026-02-02
**Projeto:** Cobranca API
**VPS:** 76.13.167.54
**GitHub:** https://github.com/Ronbragaglia/cobranca-api.git
**Status:** ✅ Push concluído com sucesso!
