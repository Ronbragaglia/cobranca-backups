# 🚀 Executar Configuração do Nginx na VPS

## 📋 Situação Atual

- ✅ Projeto enviado para o GitHub: https://github.com/Ronbragaglia/cobranca-api.git
- ✅ Projeto clonado na VPS em `/root/cobranca-api`
- ✅ PHP 8.2 instalado e rodando
- ✅ Composer install realizado
- ✅ Migrações rodadas no banco `cobranca_dev`
- ✅ Laravel rodando na porta 8082 (interna)
- ✅ Script de configuração do Nginx criado: [`scripts/configurar-nginx-proxy.sh`](scripts/configurar-nginx-proxy.sh)
- ❌ **PROBLEMA:** Não é possível acessar `http://76.13.167.54:8082` externamente (firewall do provedor bloqueando)

## 🎯 Solução: Configurar Nginx Proxy Reverso

O Nginx vai servir a aplicação na porta 80 (HTTP) e 443 (HTTPS), redirecionando para o PHP-FPM na porta 8082 (interna).

## 📝 Passos para Executar na VPS

### Opção 1: Usar o Script (Recomendado)

```bash
# 1. Acessar a VPS
ssh root@76.13.167.54

# 2. Navegar até /root
cd /root

# 3. Verificar se o script existe
ls -la scripts/configurar-nginx-proxy.sh

# 4. Executar o script
bash scripts/configurar-nginx-proxy.sh
```

### Opção 2: Executar Manualmente

```bash
# 1. Acessar a VPS
ssh root@76.13.167.54

# 2. Navegar até /root
cd /root

# 3. Verificar se o Nginx está instalado
which nginx

# Se não estiver instalado:
apt update
apt install nginx -y

# 4. Criar configuração do Nginx
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

# 5. Criar link simbólico para sites-enabled
ln -sf /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-enabled/cobranca-api

# 6. Remover configuração padrão do Nginx
rm -f /etc/nginx/sites-enabled/default

# 7. Testar configuração do Nginx
nginx -t

# 8. Reiniciar Nginx
systemctl restart nginx

# 9. Verificar status do Nginx
systemctl status nginx

# 10. Verificar se o PHP-FPM está rodando na porta 8082
netstat -tlnp | grep :8082
# OU
ss -tlnp | grep :8082

# 11. Testar a aplicação
curl -I http://76.13.167.54
```

## ✅ Verificações Após Configuração

### 1. Verificar se o Nginx está rodando:

```bash
systemctl status nginx
```

**Saída esperada:**
```
● nginx.service - A high performance web server and a reverse proxy server
   Loaded: loaded (/lib/systemd/system/nginx.service; enabled; preset: enabled)
   Active: active (running) since...
```

### 2. Verificar se o Nginx está escutando na porta 80:

```bash
netstat -tlnp | grep :80
# OU
ss -tlnp | grep :80
```

**Saída esperada:**
```
tcp        0      0  0.0.0.0:80              0.0.0.0:*               LISTEN
```

### 3. Verificar logs do Nginx:

```bash
# Logs de acesso
tail -f /var/log/nginx/cobranca-api-access.log

# Logs de erro
tail -f /var/log/nginx/cobranca-api-error.log
```

### 4. Testar a aplicação:

```bash
curl -I http://76.13.167.54
```

**Saída esperada:**
```
HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8
```

### 5. Acessar no navegador:

Abra no navegador:
```
http://76.13.167.54
```

## 🔧 Solução de Problemas

### Erro: "502 Bad Gateway"

**Causa:** O PHP-FPM não está rodando na porta 8082

**Solução:**
```bash
# Verificar se o Laravel está rodando
ps aux | grep 'php8.2 artisan serve'

# Se não estiver, reinicie:
cd /root/cobranca-api
php8.2 artisan serve --host=0.0.0.0 --port=8082 &

# Verificar se está rodando na porta 8082
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

## 📊 Resumo

1. ✅ Projeto no GitHub
2. ✅ Projeto na VPS
3. ✅ Dependências instaladas
4. ✅ Banco de dados configurado
5. ✅ Laravel rodando
6. ⏳ **PRÓXIMO:** Configurar Nginx proxy reverso

## 📄 Documentação

- **Script de configuração:** [`scripts/configurar-nginx-proxy.sh`](scripts/configurar-nginx-proxy.sh)
- **Documentação completa:** [`CONFIGURAR_NGINX_VPS.md`](CONFIGURAR_NGINX_VPS.md)
- **Guia de deploy:** [`DEPLOY_VPS_GITHUB_SUCESSO.md`](DEPLOY_VPS_GITHUB_SUCESSO.md)

---

**Data:** 2026-02-02
**Projeto:** Cobranca API
**VPS:** 76.13.167.54
**Status:** ⏳ Aguardando configuração do Nginx na VPS
