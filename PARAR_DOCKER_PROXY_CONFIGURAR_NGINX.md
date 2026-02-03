# 🛑 Parar Container Docker Proxy e Configurar Nginx

## 🎯 Objetivo

Parar o container Docker `docker-proxy` que está usando a porta 80 e configurar o Nginx para servir a aplicação Laravel corretamente.

## 🔍 Situação Atual

- ❌ Container Docker `docker-proxy` está usando a porta 80
- ✅ Nginx instalado mas não está rodando
- ❌ Porta 80 está ocupada pelo container Docker

## 🚀 Solução: Parar Container Docker e Configurar Nginx

### Passo 1: Identificar o Container Docker

```bash
# Verificar containers Docker rodando
docker ps

# Identificar o container usando a porta 80
docker ps | grep :80
```

**Saída esperada:**
```
CONTAINER ID   IMAGE     COMMAND                  CREATED       STATUS         PORTS
18121/docker-proxy   ...       ...                     ...             0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp
```

### Passo 2: Parar o Container Docker

```bash
# Parar o container docker-proxy
docker stop docker-proxy

# Verificar se foi parado
docker ps
```

**Saída esperada após parar:**
```
CONTAINER ID   IMAGE     COMMAND                  CREATED       STATUS         PORTS
```

### Passo 3: Remover o Container Docker (Opcional)

Se quiser remover permanentemente:

```bash
# Remover o container docker-proxy
docker rm docker-proxy

# Remover a imagem (se não precisar mais)
docker rmi 18121/docker-proxy
```

### Passo 4: Configurar o Nginx

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

    # Logs
    access_log /var/log/nginx/cobranca-api-access.log;
    error_log /var/log/nginx/cobranca-api-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /index.php {
        # Passar para PHP-FPM na porta 8082 (interna)
        fastcgi_pass 127.0.0.1:8082;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param PATH_INFO $fastcgi_path_info;

        # Timeout aumentado para Laravel
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Bloquear acesso a arquivos ocultos
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

# Testar configuração do Nginx
nginx -t

# Reiniciar Nginx
systemctl restart nginx
```

### Passo 5: Verificar se Está Funcionando

```bash
# Verificar se o Nginx está rodando
systemctl status nginx

# Verificar se o Nginx está escutando na porta 80
netstat -tlnp | grep :80
# OU
ss -tlnp | grep :80

# Verificar se o PHP-FPM está rodando na porta 8082
netstat -tlnp | grep :8082
# OU
ss -tlnp | grep :8082

# Testar a aplicação
curl -I http://76.13.167.54
```

## 📊 Verificação Após Configuração

### 1. Verificar se o Container Docker foi Parado

```bash
docker ps
```

**Saída esperada:**
```
CONTAINER ID   IMAGE     COMMAND                  CREATED       STATUS         PORTS
```

Se o container ainda estiver rodando, pode ser necessário forçar:

```bash
# Forçar parar o container
docker kill docker-proxy

# Remover o container
docker rm -f docker-proxy
```

### 2. Verificar se o Nginx Está Rodando

```bash
systemctl status nginx
```

**Saída esperada:**
```
● nginx.service - A high performance web server and a reverse proxy server
   Loaded: loaded (/lib/systemd/system/nginx.service; enabled; preset: enabled)
   Active: active (running) since...
```

### 3. Verificar se o Nginx Está Escutando na Porta 80

```bash
netstat -tlnp | grep :80
```

**Saída esperada:**
```
tcp        0      0  0.0.0.0:80              0.0.0.0:*               LISTEN
```

### 4. Verificar se o PHP-FPM Está Rodando na Porta 8082

```bash
netstat -tlnp | grep :8082
```

**Saída esperada:**
```
tcp        0      0  127.0.0.1:8082            0.0.0.0:*               LISTEN
```

### 5. Testar a Aplicação

```bash
curl -I http://76.13.167.54
```

**Saída esperada:**
```
HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8
```

### 6. Acessar no Navegador

Abra no navegador:
```
http://76.13.167.54
```

## 🧪 Solução de Problemas

### Problema: "Address already in use"

**Causa:** O container Docker ainda está rodando e usando a porta 80

**Solução:**
```bash
# Parar o container Docker
docker stop docker-proxy

# Verificar se foi parado
docker ps

# Reiniciar Nginx
systemctl restart nginx
```

### Problema: "502 Bad Gateway"

**Causa:** O PHP-FPM não está rodando ou não está acessível na porta 8082

**Solução:**
```bash
# Verificar se o Laravel está rodando
ps aux | grep 'php8.2 artisan serve'

# Se não estiver, reinicie:
cd /root/cobranca-api
php8.2 artisan serve --host=0.0.0.1 --port=8082 &

# Verificar se está rodando
netstat -tlnp | grep :8082
```

### Problema: "404 Not Found"

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

### Problema: "403 Forbidden"

**Causa:** Permissões incorretas nos arquivos

**Solução:**
```bash
# Configurar permissões
chmod -R 755 /root/cobranca-api
chown -R www-data:www-data /root/cobranca-api/storage /root/cobranca-api/bootstrap/cache

# Reiniciar Nginx
systemctl restart nginx
```

## 🔄 Configurar Laravel como Serviço Systemd (Recomendado)

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

## 📊 Monitoramento

### Verificar Logs do Nginx

```bash
# Logs de acesso
tail -f /var/log/nginx/cobranca-api-access.log

# Logs de erro
tail -f /var/log/nginx/cobranca-api-error.log
```

### Verificar Logs do Laravel

```bash
# Logs da aplicação
tail -f /root/cobranca-api/storage/logs/laravel.log

# Logs do PHP-FPM
tail -f /var/log/php8.2-fpm.log
```

## 📝 Resumo

1. ✅ Projeto enviado para o GitHub
2. ✅ Projeto clonado na VPS
3. ✅ Dependências instaladas
4. ✅ Migrações rodadas
5. ✅ Laravel rodando (na porta 8082 interna)
6. ❌ Container Docker `docker-proxy` usando a porta 80
7. ⏳ **PRÓXIMO:** Parar container Docker e configurar Nginx

## 🚀 Comandos para Executar na VPS

```bash
# 1. Parar o container Docker
docker stop docker-proxy

# 2. Verificar se foi parado
docker ps

# 3. Configurar o Nginx
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

# 4. Ativar o site
ln -sf /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-enabled/cobranca-api

# 5. Testar configuração
nginx -t

# 6. Reiniciar Nginx
systemctl restart nginx

# 7. Verificar status
systemctl status nginx

# 8. Verificar se está escutando na porta 80
netstat -tlnp | grep :80

# 9. Testar a aplicação
curl -I http://76.13.167.54
```

## 🔗 Links Úteis

- **Repositório GitHub:** https://github.com/Ronbragaglia/cobranca-api.git
- **Documentação Nginx:** https://nginx.org/en/docs/
- **Documentação PHP-FPM:** https://www.php.net/manual/pt_BR/install.fpm.configuration.php
- **Documentação Laravel:** https://laravel.com/docs
- **Documentação Docker:** https://docs.docker.com/

---

**Data:** 2026-02-02
**Projeto:** Cobranca API
**VPS:** 76.13.167.54
**Status:** ⏳ Aguardando parar container Docker e configurar Nginx
