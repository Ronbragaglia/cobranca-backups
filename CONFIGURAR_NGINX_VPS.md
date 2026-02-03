# 🌐 Configurar Nginx Proxy Reverso na VPS

## 🎯 Objetivo

Configurar o Nginx como proxy reverso para servir a aplicação Laravel na porta 80/443 (HTTP/HTTPS), redirecionando para o PHP-FPM que está rodando na porta 8082 (interna).

## 📋 Situação Atual

- ✅ PHP 8.2 instalado e em uso
- ✅ Projeto Laravel em `/root/cobranca-api`
- ✅ Composer install realizado
- ✅ Migrações rodadas no banco `cobranca_dev`
- ✅ Aplicação rodando com `php8.2 artisan serve --host=0.0.0.0 --port=8082`
- ✅ UFW configurado (portas 80, 443, 22, 8081, 8082 liberadas)
- ❌ **PROBLEMA:** Não é possível acessar `http://76.13.167.54:8082` externamente (ERR_CONNECTION_TIMED_OUT)

## 🔍 Diagnóstico do Problema

O problema é que o firewall do provedor de hospedagem está bloqueando a porta 8082 para acesso externo. A solução é configurar o Nginx como proxy reverso para servir a aplicação nas portas padrão 80 (HTTP) e 443 (HTTPS).

## 🚀 Solução: Nginx Proxy Reverso

### Arquitetura:

```
Internet → Nginx (Porta 80/443) → PHP-FPM (Porta 8082) → Laravel
```

O Nginx vai:
1. Receber requisições na porta 80 (HTTP) e 443 (HTTPS)
2. Passar as requisições para o PHP-FPM na porta 8082 (interna)
3. O PHP-FPM processa as requisições e retorna para o Nginx
4. O Nginx retorna a resposta para o cliente

## 📝 Passo a Passo

### 1. Enviar o script para a VPS

```bash
# No seu computador local
scp scripts/configurar-nginx-proxy.sh root@76.13.167.54:/root/scripts/
```

### 2. Acessar a VPS

```bash
ssh root@76.13.167.54
```

### 3. Executar o script de configuração

```bash
cd /root
./scripts/configurar-nginx-proxy.sh
```

O script vai:
- ✅ Verificar se o Nginx está instalado (instalar se necessário)
- ✅ Criar configuração do Nginx em `/etc/nginx/sites-available/cobranca-api`
- ✅ Ativar o site criando link simbólico em `/etc/nginx/sites-enabled/`
- ✅ Remover configuração padrão do Nginx
- ✅ Testar a configuração do Nginx
- ✅ Reiniciar o Nginx
- ✅ Verificar se o PHP-FPM está rodando na porta 8082
- ✅ Verificar status do UFW

### 4. Verificar se está funcionando

```bash
# Acessar a aplicação
curl http://76.13.167.54

# Ou abrir no navegador
# http://76.13.167.54
```

## 🔧 Configuração do Nginx (Criada pelo Script)

O script cria a seguinte configuração em `/etc/nginx/sites-available/cobranca-api`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name 76.13.167.54 _;

    # Redirecionar HTTP para HTTPS (opcional, descomente se tiver SSL)
    # return 301 https://$host$request_uri;

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
```

## 📊 Verificação Após Configuração

### Verificar se o Nginx está rodando:

```bash
systemctl status nginx
```

### Verificar logs do Nginx:

```bash
# Logs de acesso
tail -f /var/log/nginx/cobranca-api-access.log

# Logs de erro
tail -f /var/log/nginx/cobranca-api-error.log
```

### Verificar se o PHP-FPM está rodando:

```bash
netstat -tlnp | grep :8082
# OU
ss -tlnp | grep :8082
```

### Verificar se o Laravel está rodando:

```bash
ps aux | grep 'php8.2 artisan serve'
```

### Testar a aplicação:

```bash
curl -I http://76.13.167.54
```

## 🔒️ Configurar HTTPS (SSL) com Let's Encrypt

Após configurar o Nginx e verificar que está funcionando, você pode configurar SSL:

```bash
# Instalar Certbot
apt update
apt install certbot python3-certbot-nginx -y

# Obter certificado SSL
certbot --nginx -d 76.13.167.54

# Renovar automaticamente
certbot renew --dry-run
```

## 🔧 Solução de Problemas

### Erro: "502 Bad Gateway"

**Causa:** O PHP-FPM não está rodando ou não está acessível na porta 8082

**Solução:**
```bash
# Verificar se o Laravel está rodando
ps aux | grep 'php8.2 artisan serve'

# Se não estiver, reinicie:
cd /root/cobranca-api
php8.2 artisan serve --host=0.0.0.0 --port=8082 &

# OU configurar o PHP-FPM como serviço systemd
nano /etc/systemd/system/laravel.service
```

### Erro: "404 Not Found"

**Causa:** O caminho `root` na configuração do Nginx está incorreto

**Solução:**
```bash
# Editar configuração do Nginx
nano /etc/nginx/sites-available/cobranca-api

# Verificar se o caminho está correto
root /root/cobranca-api/public;
```

### Erro: "403 Forbidden"

**Causa:** Permissões incorretas nos arquivos

**Solução:**
```bash
# Configurar permissões
chmod -R 755 /root/cobranca-api
chown -R www-data:www-data /root/cobranca-api/storage /root/cobranca-api/bootstrap/cache
```

### Erro: "Connection timed out"

**Causa:** O firewall do provedor está bloqueando a porta 80

**Solução:**
```bash
# Verificar se o UFW está bloqueando
ufw status

# Se necessário, liberar a porta 80
ufw allow 80/tcp
ufw allow 443/tcp

# Verificar se o Nginx está escutando na porta 80
netstat -tlnp | grep :80
# OU
ss -tlnp | grep :80
```

## 🔄 Configurar Laravel como Serviço Systemd (Recomendado)

Para garantir que o Laravel inicie automaticamente, configure como serviço systemd:

```bash
# Criar arquivo de serviço
nano /etc/systemd/system/laravel.service
```

**Conteúdo do arquivo:**
```ini
[Unit]
Description=Laravel Application
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/root/cobranca-api
ExecStart=/usr/bin/php8.2 /root/cobranca-api/artisan serve --host=0.0.0.0 --port=8082
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

**Ativar o serviço:**
```bash
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
5. ✅ Laravel rodando na porta 8082 (interna)
6. ⏳ **PRÓXIMO:** Configurar Nginx proxy reverso

## 🚀 Comandos para Executar na VPS

```bash
# 1. Enviar o script (do seu computador)
scp scripts/configurar-nginx-proxy.sh root@76.13.167.54:/root/scripts/

# 2. Acessar a VPS
ssh root@76.13.167.54

# 3. Executar o script
cd /root
./scripts/configurar-nginx-proxy.sh

# 4. Verificar se está funcionando
curl http://76.13.167.54
```

## 📄 Documentação Adicional

- **Documentação Nginx:** https://nginx.org/en/docs/
- **Documentação PHP-FPM:** https://www.php.net/manual/pt_BR/install.fpm.configuration.php
- **Documentação Laravel:** https://laravel.com/docs

---

**Data:** 2026-02-02
**Projeto:** Cobranca API
**VPS:** 76.13.167.54
**Status:** ⏳ Aguardando configuração do Nginx
