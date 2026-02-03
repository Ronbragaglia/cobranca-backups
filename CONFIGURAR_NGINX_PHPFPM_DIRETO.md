# 🌐 Configurar Nginx para Laravel com PHP-FPM Direto

## 🎯 Objetivo

Configurar o Nginx para apontar diretamente para o serviço PHP-FPM/Laravel que está rodando na porta 8082 (interna), ou para usar um domínio customizado como `cobrancaauto.meudominio.com`.

## 🔍 Situação Atual

- **Container Traefik:** Usando portas 80/443
- **PHP-FPM/Laravel:** Rodando em `/root/cobranca-api` na porta 8082 (interna)
- **Nginx:** Instalado mas não configurado corretamente
- **Problema:** Nginx está tentando usar a porta 8082 que já está em uso pelo PHP-FPM

## 🚀 Solução 1: Apontar para PHP-FPM Direto (Recomendado)

Configurar o Nginx para passar as requisições diretamente para o PHP-FPM na porta 8082:

```bash
# Acessar a VPS
ssh root@76.13.167.54

# Criar configuração do Nginx
cat > /etc/nginx/sites-available/cobranca-api << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name 76.13.167.54 _;

    root /root/cobranca-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php index.html index.htm;

    charset utf-8;

    access_log /var/log/nginx/cobranca-api-access.log;
    error_log /var/log/nginx/cobranca-api-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /index.php {
        # Passar diretamente para o PHP-FPM na porta 8082
        fastcgi_pass 127.0.0.1:8082;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param PATH_INFO $fastcgi_path_info;

        # Timeout aumentado para Laravel
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    location ~ /\. {
        deny all;
    }

    # Configurações de upload (para arquivos grandes)
    client_max_body_size 100M;
    fastcgi_buffer_size 128k;
    fastcgi_buffers 4 256k;
}
EOF

# Ativar o site
ln -sf /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-enabled/cobranca-api

# Remover configuração padrão
rm -f /etc/nginx/sites-enabled/default

# Testar configuração
nginx -t

# Reiniciar Nginx
systemctl restart nginx

# Verificar status
systemctl status nginx
```

## 🚀 Solução 2: Usar Domínio Customizado

Se você tiver um domínio como `cobrancaauto.meudominio.com`, configure o Nginx para usar esse domínio:

```bash
# Editar configuração do Nginx
nano /etc/nginx/sites-available/cobranca-api
```

**Configuração com domínio:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name cobrancaauto.meudominio.com www.cobrancaauto.meudominio.com;

    root /root/cobranca-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php index.html index.htm;

    charset utf-8;

    access_log /var/log/nginx/cobranca-api-access.log;
    error_log /var/log/nginx/cobranca-api-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /index.php {
        # Passar para o PHP-FPM na porta 8082
        fastcgi_pass 127.0.0.1:8082;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param PATH_INFO $fastcgi_path_info;

        # Timeout aumentado para Laravel
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    location ~ /\. {
        deny all;
    }

    # Configurações de upload (para arquivos grandes)
    client_max_body_size 100M;
    fastcgi_buffer_size 128k;
    fastcgi_buffers 4 256k;
}
```

## 🚀 Solução 3: Desativar Traefik (Se Não Necessário)

Se você não precisar do Traefik, pode desativá-lo:

```bash
# Parar o container Traefik
docker stop traefik

# Desabilitar o serviço
systemctl disable traefik

# Remover o serviço (opcional)
# systemctl disable traefik
```

## 🚀 Solução 4: Configurar Nginx para Ambos (IP e Domínio)

Configurar o Nginx para responder tanto pelo IP quanto pelo domínio:

```bash
cat > /etc/nginx/sites-available/cobranca-api << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name 76.13.167.54 cobrancaauto.meudominio.com www.cobrancaauto.meudominio.com;

    root /root/cobranca-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php index.html index.htm;

    charset utf-8;

    access_log /var/log/nginx/cobranca-api-access.log;
    error_log /var/log/nginx/cobranca-api-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /index.php {
        # Passar para o PHP-FPM na porta 8082
        fastcgi_pass 127.0.0.1:8082;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param PATH_INFO $fastcgi_path_info;

        # Timeout aumentado para Laravel
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    location ~ /\. {
        deny all;
    }

    # Configurações de upload (para arquivos grandes)
    client_max_body_size 100M;
    fastcgi_buffer_size 128k;
    fastcgi_buffers 4 256k;
}
EOF

# Ativar o site
ln -sf /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-enabled/cobranca-api

# Remover configuração padrão
rm -f /etc/nginx/sites-enabled/default

# Testar configuração
nginx -t

# Reiniciar Nginx
systemctl restart nginx

# Verificar status
systemctl status nginx
```

## 🔒️ Configurar HTTPS (SSL) com Let's Encrypt

Após configurar o Nginx e verificar que está funcionando, você pode configurar SSL:

```bash
# Instalar Certbot
apt update
apt install certbot python3-certbot-nginx -y

# Obter certificado SSL (para o domínio)
certbot --nginx -d cobrancaauto.meudominio.com

# OU obter certificado SSL (para o IP)
certbot --nginx -d 76.13.167.54 --standalone

# Renovar automaticamente
certbot renew --dry-run
```

## 📊 Verificação Após Configuração

### Verificar se o Nginx está rodando:

```bash
systemctl status nginx
```

**Saída esperada:**
```
● nginx.service - A high performance web server and a reverse proxy server
   Loaded: loaded (/lib/systemd/system/nginx.service; enabled; preset: enabled)
   Active: active (running) since...
```

### Verificar se o Nginx está escutando na porta 80:

```bash
netstat -tlnp | grep :80
# OU
ss -tlnp | grep :80
```

**Saída esperada:**
```
tcp        0      0  0.0.0.0:80              0.0.0.0:*               LISTEN
```

### Verificar se o PHP-FPM está rodando na porta 8082:

```bash
netstat -tlnp | grep :8082
# OU
ss -tlnp | grep :8082
```

**Saída esperada:**
```
tcp        0      0  0.0.0.0:8082             0.0.0.0:*               LISTEN
```

### Testar a aplicação:

```bash
curl -I http://76.13.167.54
```

**Saída esperada:**
```
HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8
```

### Acessar no navegador:

```
http://76.13.167.54
```

## 🧪 Solução de Problemas

### Erro: "502 Bad Gateway"

**Causa:** O PHP-FPM não está rodando na porta 8082

**Solução:**
```bash
# Verificar se o Laravel está rodando
ps aux | grep 'php8.2 artisan serve'

# Se não estiver, reinicie:
cd /root/cobranca-api
php8.2 artisan serve --host=0.0.0.1 --port=8082 &

# Verificar se o PHP-FPM está rodando
netstat -tlnp | grep :8082
```

### Erro: "404 Not Found"

**Causa:** O caminho `root` na configuração do Nginx está incorreto

**Solução:**
```bash
# Editar configuração do Nginx
nano /etc/nginx/sites-available/cobranca-api

# Verificar se o caminho está correto
root /root/cobranca-api/public;

# Salvar e sair (Ctrl+X, Y, Enter)

# Testar configuração
nginx -t

# Reiniciar Nginx
systemctl restart nginx
```

### Erro: "403 Forbidden"

**Causa:** Permissões incorretas nos arquivos

**Solução:**
```bash
# Configurar permissões
chmod -R 755 /root/cobranca-api
chown -R www-data:www-data /root/cobranca-api/storage /root/cobranca-api/bootstrap/cache

# Reiniciar Nginx
systemctl restart nginx
```

## 🔄 Configurar Laravel como Serviço Systemd

Para garantir que o Laravel inicie automaticamente, configure como serviço systemd:

```bash
# Criar arquivo de serviço
cat > /etc/systemd/system/laravel.service << 'EOF'
[Unit]
Description=Laravel Application
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/root/cobranca-api
ExecStart=/usr/bin/php8.2 /root/cobranca-api/artisan serve --host=0.0.0.1 --port=8082
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

# Recarregar systemd
systemctl daemon-reload

# Habilitar o serviço
systemctl enable laravel.service

# Iniciar o serviço
systemctl start laravel.service

# Verificar status
systemctl status laravel.service
```

## 📝 Resumo

1. ✅ Projeto enviado para o GitHub
2. ✅ Projeto clonado na VPS
3. ✅ Dependências instaladas
4. ✅ Migrações rodadas
5. ✅ Laravel rodando (porta 8082 interna)
6. ⏳ **PRÓXIMO:** Configurar Nginx para apontar para PHP-FPM

## 🚀 Comandos para Executar na VPS

### Opção 1: Apontar para PHP-FPM Direto (Recomendado)

```bash
# Criar configuração do Nginx
cat > /etc/nginx/sites-available/cobranca-api << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name 76.13.167.54 _;

    root /root/cobranca-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php index.html index.htm;

    charset utf-8;

    access_log /var/log/nginx/cobranca-api-access.log;
    error_log /var/log/nginx/cobranca-api-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /index.php {
        fastcgi_pass 127.0.0.1:8082;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param PATH_INFO $fastcgi_path_info;

        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    location ~ /\. {
        deny all;
    }

    client_max_body_size 100M;
    fastcgi_buffer_size 128k;
    fastcgi_buffers 4 256k;
}
EOF

# Ativar o site
ln -sf /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-enabled/cobranca-api

# Remover configuração padrão
rm -f /etc/nginx/sites-enabled/default

# Testar configuração
nginx -t

# Reiniciar Nginx
systemctl restart nginx

# Verificar status
systemctl status nginx

# Testar a aplicação
curl -I http://76.13.167.54
```

### Opção 2: Usar Domínio Customizado

```bash
# Editar configuração do Nginx
nano /etc/nginx/sites-available/cobranca-api
```

**Substituir `server_name 76.13.167.54 _;` por:**
```nginx
server_name cobrancaauto.meudominio.com www.cobrancaauto.meudominio.com;
```

### Opção 3: Desativar Traefik (Se Não Necessário)

```bash
# Parar o container Traefik
docker stop traefik

# Desabilitar o serviço
systemctl disable traefik
```

### Opção 4: Configurar Laravel como Serviço Systemd

```bash
# Criar arquivo de serviço
cat > /etc/systemd/system/laravel.service << 'EOF'
[Unit]
Description=Laravel Application
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/root/cobranca-api
ExecStart=/usr/bin/php8.2 /root/cobranca-api/artisan serve --host=0.0.0.1 --port=8082
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

# Recarregar systemd
systemctl daemon-reload

# Habilitar o serviço
systemctl enable laravel.service

# Iniciar o serviço
systemctl start laravel.service

# Verificar status
systemctl status laravel.service
```

## 📄 Documentação

- **Documentação Nginx:** https://nginx.org/en/docs/
- **Documentação PHP-FPM:** https://www.php.net/manual/pt_BR/install.fpm.configuration.php
- **Documentação Laravel:** https://laravel.com/docs
- **Documentação Systemd:** https://www.freedesktop.org/wiki/Software/systemd/Service

---

**Data:** 2026-02-02
**Projeto:** Cobranca API
**VPS:** 76.13.167.54
**Status:** ⏳ Aguardando configuração do Nginx
