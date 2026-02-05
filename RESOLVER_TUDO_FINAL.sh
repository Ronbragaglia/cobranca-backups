#!/bin/bash

echo "🚀 RESOLVENDO TUDO E TRAZENDO O SITE DE VOLTA..."

# Passo 1: Ir para o diretório correto
cd /var/www/cobranca-api

# Passo 2: Parar o Nginx do sistema
echo "📋 Parando o Nginx do sistema..."
systemctl stop nginx
systemctl disable nginx

# Passo 3: Parar e remover o container cobranca_nginx atual
echo "📋 Parando e removendo o container cobranca_nginx atual..."
docker stop cobranca_nginx
docker rm cobranca_nginx

# Passo 4: Verificar se o arquivo index.php existe
echo "📋 Verificando se o arquivo index.php existe..."
if [ ! -f /var/www/cobranca-api/public/index.php ]; then
    echo "❌ Arquivo index.php não existe. Criando..."
    cat > /var/www/cobranca-api/public/index.php << 'INDEXPHP'
<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
INDEXPHP
    chmod 644 /var/www/cobranca-api/public/index.php
    chown root:root /var/www/cobranca-api/public/index.php
else
    echo "✅ Arquivo index.php existe."
fi

# Passo 5: Modificar PHP-FPM para escutar em IPv4
echo "📋 Modificando PHP-FPM para escutar em IPv4..."
docker exec cobranca_app cp /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf.backup
docker exec cobranca_app sh -c 'cat > /usr/local/etc/php-fpm.d/www.conf << '\''EOF'\''
[www]
user = www-data
group = www-data
listen = 0.0.0.0:9000
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
php_admin_value[error_log] = /var/log/php-fpm.log
php_admin_flag[log_errors] = on
EOF'

# Passo 6: Reiniciar o container app
echo "📋 Reiniciando o container app..."
docker restart cobranca_app

# Passo 7: Aguardar 15 segundos
echo "⏳ Aguardando 15 segundos..."
sleep 15

# Passo 8: Verificar se o PHP-FPM está escutando em IPv4
echo "📋 Verificando se o PHP-FPM está escutando em IPv4..."
docker exec cobranca_app netstat -tlnp | grep 9000

# Passo 9: Criar configuração do Nginx com SSL
echo "📋 Criando configuração do Nginx com SSL..."
cat > /var/www/cobranca-api/nginx-ssl.conf << 'NGINXCONF'
server {
    listen 80;
    listen [::]:80;
    server_name api.cobrancaauto.com.br;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.cobrancaauto.com.br;

    ssl_certificate /etc/letsencrypt/live/api.cobrancaauto.com.br/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.cobrancaauto.com.br/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    root /var/www/public;
    index index.php index.html;

    access_log /var/log/nginx/cobranca-api.access.log;
    error_log /var/log/nginx/cobranca-api.error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass cobranca_app:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location ~ /\. {
        deny all;
    }
}
NGINXCONF

# Passo 10: Criar novo container cobranca_nginx com SSL
echo "📋 Criando novo container cobranca_nginx com SSL..."
docker run -d \
  --name cobranca_nginx \
  --restart unless-stopped \
  -p 80:80 \
  -p 443:443 \
  -v /var/www/cobranca-api:/var/www \
  -v /var/www/cobranca-api/nginx-ssl.conf:/etc/nginx/conf.d/default.conf \
  -v /etc/letsencrypt:/etc/letsencrypt:ro \
  --network cobranca-api_cobranca_network \
  nginx:alpine

# Passo 11: Aguardar 10 segundos
echo "⏳ Aguardando 10 segundos..."
sleep 10

# Passo 12: Verificar se o container está rodando
echo "📋 Verificando se o container está rodando..."
docker ps | grep nginx

# Passo 13: Verificar se as portas 80 e 443 estão escutando
echo "📋 Verificando se as portas 80 e 443 estão escutando..."
netstat -tlnp | grep -E ":(80|443)"

# Passo 14: Verificar logs do Nginx
echo "📋 Verificando logs do Nginx..."
docker logs cobranca_nginx --tail 20

# Passo 15: Verificar logs do app
echo "📋 Verificando logs do app..."
docker logs cobranca_app --tail 20

# Passo 16: Testar o site
echo "📋 Testando o site..."
echo ""
echo "=== Testando HTTP ==="
curl -I http://localhost
echo ""
echo "=== Testando HTTPS ==="
curl -I https://api.cobrancaauto.com.br/

echo ""
echo "✅ Resolução concluída!"
echo "📋 Se o site não estiver funcionando, verifique os logs acima."
