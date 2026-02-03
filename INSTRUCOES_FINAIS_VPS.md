# 🚀 Instruções Finais - Deploy CobrancaAuto VPS

## ✅ Status Atual

O projeto foi enviado com sucesso para o GitHub e está configurado na VPS!

### 📦 Repositório GitHub:
- **URL:** https://github.com/Ronbragaglia/cobranca-api.git
- **Branch:** main
- **Commit:** "Deploy inicial CobrancaAuto VPS"
- **Arquivos:** 263 arquivos (38,116 inserções)

### 🖥️ VPS Configurada:
- **IP:** 76.13.167.54
- **PHP:** 8.2 instalado e rodando
- **MySQL:** 8.0 rodando
- **Banco:** cobranca_dev configurado
- **Projeto:** /root/cobranca-api
- **Laravel:** Rodando na porta 8082 (interna)
- **Composer:** Instalado
- **Migrações:** Rodadas

### ⚠️ Problema Atual:
- **NÃO é possível acessar:** http://76.13.167.54:8082 externamente
- **Causa:** Firewall do provedor está bloqueando a porta 8082
- **Solução:** Configurar Nginx como proxy reverso (porta 80 → 8082)

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

### Passo 1: Copiar o Script para a VPS

No seu computador local:

```bash
scp scripts/configurar-nginx-simples.sh root@76.13.167.54:/root/
```

### Passo 2: Acessar a VPS

```bash
ssh root@76.13.167.54
```

### Passo 3: Executar o Script

```bash
cd /root
bash configurar-nginx-simples.sh
```

O script vai:
- ✅ Verificar se está rodando como root
- ✅ Instalar Nginx (se necessário)
- ✅ Criar configuração do Nginx
- ✅ Ativar o site
- ✅ Remover configuração padrão
- ✅ Testar configuração do Nginx
- ✅ Reiniciar Nginx
- ✅ Verificar status do Nginx
- ✅ Verificar se o PHP-FPM está rodando na porta 8082

### Passo 4: Verificar se Está Funcionando

Após executar o script, verifique:

```bash
# 1. Verificar se o Nginx está rodando
systemctl status nginx

# 2. Verificar se o Nginx está escutando na porta 80
netstat -tlnp | grep :80

# 3. Testar a aplicação
curl -I http://76.13.167.54

# 4. Acessar no navegador
# http://76.13.167.54
```

## 📋 O que o Script Faz

O script [`scripts/configurar-nginx-simples.sh`](scripts/configurar-nginx-simples.sh) configura o Nginx para:

1. **Servir na porta 80 (HTTP)**
2. **Redirecionar para o PHP-FPM na porta 8082 (interna)**
3. **Apontar para `/root/cobranca-api/public`**
4. **Configurar timeout aumentado (300s)**
5. **Configurar upload de arquivos grandes (100M)**

## 🔧 Configuração do Nginx (Criada pelo Script)

O script cria esta configuração em `/etc/nginx/sites-available/cobranca-api`:

```nginx
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

    location ~ /\. {
        deny all;
    }

    client_max_body_size 100M;
    fastcgi_buffer_size 128k;
    fastcgi_buffers 4 256k;
}
```

## 🔒️ Configurar HTTPS (SSL) com Let's Encrypt (Opcional)

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

## 📊 Monitoramento

### Verificar Logs do Nginx:

```bash
# Logs de acesso
tail -f /var/log/nginx/cobranca-api-access.log

# Logs de erro
tail -f /var/log/nginx/cobranca-api-error.log
```

### Verificar Processos:

```bash
# Verificar se o Nginx está rodando
systemctl status nginx

# Verificar se o PHP-FPM está rodando
systemctl status php8.2-fpm

# Verificar se o Laravel está rodando
ps aux | grep 'php8.2 artisan serve'
```

## 🧪 Solução de Problemas

### Erro: "502 Bad Gateway"

**Causa:** O PHP-FPM não está rodando ou não está acessível na porta 8082

**Solução:**
```bash
# Verificar se o Laravel está rodando
ps aux | grep 'php8.2 artisan serve'

# Se não estiver, reinicie:
cd /root/cobranca-api
php8.2 artisan serve --host=0.0.0.0 --port=8082 &

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

### Opção 1: Usar o Script (Recomendado)

```bash
# 1. Copiar o script para a VPS (do seu computador)
scp scripts/configurar-nginx-simples.sh root@76.13.167.54:/root/

# 2. Acessar a VPS
ssh root@76.13.167.54

# 3. Executar o script
cd /root
bash configurar-nginx-simples.sh

# 4. Verificar se está funcionando
curl -I http://76.13.167.54
```

### Opção 2: Executar Manualmente

```bash
# 1. Acessar a VPS
ssh root@76.13.167.54

# 2. Navegar até /root
cd /root

# 3. Verificar se o Nginx está instalado
which nginx

# Se não estiver, instale:
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

# 11. Testar a aplicação
curl -I http://76.13.167.54
```

## 📄 Arquivos Criados

1. **[`scripts/configurar-nginx-simples.sh`](scripts/configurar-nginx-simples.sh)** - Script simplificado de configuração do Nginx
2. **[`scripts/configurar-nginx-proxy.sh`](scripts/configurar-nginx-proxy.sh)** - Script completo de configuração do Nginx
3. **[`CONFIGURAR_NGINX_VPS.md`](CONFIGURAR_NGINX_VPS.md)** - Documentação completa de configuração
4. **[`EXECUTAR_NA_VPS_AGORA.md`](EXECUTAR_NA_VPS_AGORA.md)** - Instruções passo a passo

## 🎯 Resultado Esperado

Após configurar o Nginx, você deve ser capaz de:

1. ✅ Acessar a aplicação em: `http://76.13.167.54`
2. ✅ O Nginx vai servir a aplicação na porta 80 (HTTP)
3. ✅ O Nginx vai redirecionar para o PHP-FPM na porta 8082 (interna)
4. ✅ O firewall do provedor não vai mais bloquear o acesso

## 🔗 Links Úteis

- **Repositório GitHub:** https://github.com/Ronbragaglia/cobranca-api.git
- **Documentação Nginx:** https://nginx.org/en/docs/
- **Documentação PHP-FPM:** https://www.php.net/manual/pt_BR/install.fpm.configuration.php
- **Documentação Laravel:** https://laravel.com/docs

---

**Data:** 2026-02-02
**Projeto:** Cobranca API
**VPS:** 76.13.167.54
**Status:** ⏳ Aguardando configuração do Nginx proxy reverso
