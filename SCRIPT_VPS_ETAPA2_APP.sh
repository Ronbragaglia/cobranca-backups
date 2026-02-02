#!/bin/bash
# ============================================
# DEPLOY DO APP LARAVEL COM TRAEFIK - VPS
# ============================================
# VPS: 76.13.167.54 | srv1298946
# Diretório: /opt/app
# ============================================

set -e

echo "============================================"
echo "INICIANDO DEPLOY DO APP LARAVEL"
echo "============================================"
echo ""

# Criar diretório do app
echo "1. Criando diretório /opt/app..."
mkdir -p /opt/app
cd /opt/app

# Criar docker-compose.yml
echo "2. Criando docker-compose.yml..."
cat > docker-compose.yml << 'DOCKEREOF'
version: '3.8'

services:
  # PHP-FPM 8.4
  php:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: cobranca-php
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./html:/var/www
      - ./storage:/var/www/storage
      - ./bootstrap/cache:/var/www/bootstrap/cache
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - APP_URL=https://cobrancaauto.com.br
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=cobranca
      - DB_USERNAME=cobranca_user
      - DB_PASSWORD=__SET_IN_SERVER_ONLY__
    networks:
      - app_network
      - traefik_default

  # Nginx
  nginx:
    image: nginx:alpine
    container_name: cobranca-nginx
    restart: unless-stopped
    ports:
      - "8081:80"
    volumes:
      - ./html:/var/www
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php
    networks:
      - app_network
      - traefik_default
    labels:
      - "traefik.enable=true"
      - "traefik.docker.network=traefik_default"
      - "traefik.http.routers.cobranca.rule=Host(`cobrancaauto.com.br`,`www.cobrancaauto.com.br`,`app.cobrancaauto.com.br`,`api.cobrancaauto.com.br`)"
      - "traefik.http.routers.cobranca.entrypoints=websecure"
      - "traefik.http.routers.cobranca.tls=true"
      - "traefik.http.routers.cobranca.tls.certresolver=cloudflare"
      - "traefik.http.services.cobranca.loadbalancer.server.port=80"

  # MySQL 8.0
  mysql:
    image: mysql:8.0
    container_name: cobranca-mysql
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: __SET_IN_SERVER_ONLY__
      MYSQL_DATABASE: cobranca
      MYSQL_USER: cobranca_user
      MYSQL_PASSWORD: __SET_IN_SERVER_ONLY__
    volumes:
      - mysql_data:/var/lib/mysql
      - ./init.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"
    networks:
      - app_network
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p__SET_IN_SERVER_ONLY__"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Redis
  redis:
    image: redis:7-alpine
    container_name: cobranca-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - app_network
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

networks:
  app_network:
    driver: bridge
  traefik_default:
    external: true

volumes:
  mysql_data:
DOCKEREOF

# Criar nginx.conf
echo "3. Criando nginx.conf..."
cat > nginx.conf << 'NGINXEOF'
server {
    listen 80;
    server_name cobrancaauto.com.br www.cobrancaauto.com.br app.cobrancaauto.com.br api.cobrancaauto.com.br;
    root /var/www/public;
    index index.php index.html;

    # Logs
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    # Max upload size
    client_max_body_size 100M;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Laravel location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
        fastcgi_read_timeout 300;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
NGINXEOF

# Criar diretórios
echo "4. Criando diretórios..."
mkdir -p html
mkdir -p storage
mkdir -p bootstrap/cache

# Criar .env
echo "5. Criando .env..."
cat > html/.env << 'ENVEOF'
APP_NAME="Cobranca API"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE_REPLACE_THIS
APP_DEBUG=false
APP_URL=https://cobrancaauto.com.br

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cobranca
DB_USERNAME=cobranca_user
DB_PASSWORD=__SET_IN_SERVER_ONLY__

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

EVOLUTION_API_URL=http://evolution-api:8080
EVOLUTION_API_USERNAME=admin
EVOLUTION_API_PASSWORD=admin123
ENVEOF

# Criar init.sql
echo "6. Criando init.sql..."
cat > init.sql << 'SQLEOF'
CREATE DATABASE IF NOT EXISTS cobranca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cobranca;
CREATE USER IF NOT EXISTS 'cobranca_user'@'%' IDENTIFIED BY '__SET_IN_SERVER_ONLY__';
GRANT ALL PRIVILEGES ON cobranca.* TO 'cobranca_user'@'%';
FLUSH PRIVILEGES;
SQLEOF

# Configurar permissões
echo "7. Configurando permissões..."
chmod -R 755 html
chmod -R 775 html/storage
chmod -R 775 html/bootstrap/cache
chown -R www-data:www-data html/storage 2>/dev/null || true
chown -R www-data:www-data html/bootstrap/cache 2>/dev/null || true

echo ""
echo "============================================"
echo "ARQUIVOS CRIADOS COM SUCESSO!"
echo "============================================"
echo ""
echo "Diretório: /opt/app"
echo "Arquivos criados:"
echo "  - docker-compose.yml"
echo "  - nginx.conf"
echo "  - html/.env"
echo "  - init.sql"
echo ""
echo "============================================"
echo "PRÓXIMO PASSO: COPIAR O CÓDIGO DO APP"
echo "============================================"
echo ""
echo "Execute no seu terminal LOCAL (não na VPS):"
echo "  scp -r /home/admin/projects/cobranca-api/* root@76.13.167.54:/opt/app/html/"
echo ""
echo "Ou faça upload via SFTP/FTP do código para /opt/app/html/"
echo ""
echo "Após copiar o código, execute:"
echo "  cd /opt/app"
echo "  docker compose build"
echo "  docker compose up -d"
echo "  docker compose exec php php artisan migrate --force"
echo "  docker compose exec php php artisan storage:link"
echo "  docker compose exec php php artisan cache:clear"
echo "  docker compose exec php php artisan config:clear"
echo "  docker compose exec php php artisan route:clear"
echo "  docker compose exec php php artisan view:clear"
echo ""
echo "============================================"
echo "FIM DO SCRIPT"
echo "============================================"
