# 🚀 Instruções para Finalizar a Configuração da VPS

## 📋 Visão Geral

Este script completo finaliza a configuração da VPS para a aplicação Cobrança-API, resolvendo todos os problemas de configuração e preparando o ambiente para produção.

## 🎯 O que o script faz

1. ✅ **Resolve conflito de porta 80** - Para containers docker-proxy que estão usando a porta 80
2. ✅ **Inicia MySQL** - Sobe o container MySQL com docker-compose
3. ✅ **Configura Nginx** - Configura o Nginx para o domínio `api.cobrancaauto.com.br`
4. ✅ **Configura HTTPS** - Instala e configura Certbot para SSL/TLS
5. ✅ **Configura Trust Proxies** - Configura Laravel para confiar em proxies Cloudflare
6. ✅ **Configura .env** - Cria e configura o arquivo .env de produção
7. ✅ **Executa migrações** - Roda as migrações do banco de dados
8. ✅ **Configura permissões** - Ajusta permissões de diretórios
9. ✅ **Verifica status** - Verifica o status final de todos os serviços

## 📁 Arquivos Criados

- [`scripts/finalizar-cobranca-api-vps.sh`](scripts/finalizar-cobranca-api-vps.sh) - Script executável
- [`SCRIPT_FINALIZAR_COBRANCA_API_VPS_COMPLETO.txt`](SCRIPT_FINALIZAR_COBRANCA_API_VPS_COMPLETO.txt) - Script em texto para copiar

## 🚀 Como Executar

### Opção 1: Usar o script do projeto (RECOMENDADO)

Se você já clonou o projeto na VPS:

```bash
# 1. Acessar a VPS
ssh root@76.13.167.54

# 2. Ir para o diretório do projeto
cd /var/www/cobranca-api

# 3. Dar permissão de execução
chmod +x scripts/finalizar-cobranca-api-vps.sh

# 4. Executar o script
./scripts/finalizar-cobranca-api-vps.sh
```

### Opção 2: Criar o script manualmente

```bash
# 1. Acessar a VPS
ssh root@76.13.167.54

# 2. Criar o arquivo
nano /root/finalizar-cobranca-api-vps.sh

# 3. Colar o conteúdo do SCRIPT_FINALIZAR_COBRANCA_API_VPS_COMPLETO.txt
#    (Ctrl+Shift+V para colar)

# 4. Salvar e sair (Ctrl+X, Y, Enter)

# 5. Dar permissão de execução
chmod +x /root/finalizar-cobranca-api-vps.sh

# 6. Executar o script
/root/finalizar-cobranca-api-vps.sh
```

## ⚙️ Configurações do Script

O script usa as seguintes configurações padrão:

- **Caminho do projeto:** `/var/www/cobranca-api`
- **Domínio:** `api.cobrancaauto.com.br`
- **Email para Certbot:** `seu@email.com`

### Personalizar configurações

Se precisar alterar essas configurações, edite o script antes de executar:

```bash
nano /root/finalizar-cobranca-api-vps.sh
```

Altere as variáveis no início do script:

```bash
PROJECT_PATH="/var/www/cobranca-api"
DOMAIN="api.cobrancaauto.com.br"
EMAIL="seu@email.com"
```

## 📝 Passos Manuais do Script

Se preferir executar os passos manualmente:

### Passo 1: Resolver conflito de porta 80

```bash
# Identificar containers usando porta 80
docker ps -a --filter "publish=80"

# Parar containers
docker stop <NOME_CONTAINER>
docker rm <NOME_CONTAINER>
```

### Passo 2: Subir MySQL

```bash
cd /var/www/cobranca-api
docker compose -f docker-compose.mysql.yml up -d
sleep 10
docker compose -f docker-compose.mysql.yml logs --tail=10
```

### Passo 3: Configurar Nginx

```bash
# Criar configuração
cat > /etc/nginx/sites-available/cobranca-api << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name api.cobrancaauto.com.br www.api.cobrancaauto.com.br;

    root /var/www/cobranca-api/public;

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

    location ~ \.php$ {
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
rm -f /etc/nginx/sites-enabled/default

# Testar e reiniciar
nginx -t
systemctl restart nginx
```

### Passo 4: Configurar HTTPS

```bash
apt update
apt install -y certbot python3-certbot-nginx
certbot --nginx -d api.cobrancaauto.com.br -d www.api.cobrancaauto.com.br --non-interactive --agree-tos --email seu@email.com
```

### Passo 5: Configurar Trust Proxies

```bash
cd /var/www/cobranca-api

# Fazer backup
cp bootstrap/app.php bootstrap/app.php.backup

# Adicionar configuração de trust proxies
# Edite o arquivo bootstrap/app.php e adicione dentro do ->withMiddleware():
# $middleware->trustProxies(at: ['*']);

php artisan config:clear
php artisan config:cache
```

### Passo 6: Configurar .env

```bash
cd /var/www/cobranca-api

# Criar .env se não existir
cp .env.example .env

# Gerar APP_KEY
php artisan key:generate

# Editar .env manualmente
nano .env
```

Configure as variáveis importantes:
- `DB_PASSWORD` - Senha do MySQL
- `APP_URL=https://api.cobrancaauto.com.br`
- `MAIL_*` - Configurações de email

### Passo 7: Executar migrações

```bash
cd /var/www/cobranca-api
php artisan migrate --force
php artisan db:seed --force
```

### Passo 8: Configurar permissões

```bash
cd /var/www/cobranca-api
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## ✅ Verificação

Após executar o script, verifique:

```bash
# Status do Nginx
systemctl status nginx

# Containers Docker
docker ps

# Portas em uso
netstat -tlnp | grep -E ':(80|443|3306|8082)'

# Testar acesso
curl -I https://api.cobrancaauto.com.br
```

## 🔧 Troubleshooting

### Erro: "nginx: [emerg] bind() to 0.0.0.0:80 failed"

**Causa:** Ainda há um processo usando a porta 80

**Solução:**
```bash
# Verificar processo
netstat -tlnp | grep :80

# Matar processo (CUIDADO!)
kill -9 <PID>
```

### Erro: "502 Bad Gateway"

**Causa:** Laravel não está rodando na porta 8082

**Solução:**
```bash
# Verificar se Laravel está rodando
ps aux | grep 'php.*artisan serve'

# Iniciar Laravel
cd /var/www/cobranca-api
php artisan serve --host=0.0.0.1 --port=8082 &
```

### Erro: "Connection refused" no MySQL

**Causa:** MySQL não está rodando

**Solução:**
```bash
# Verificar status do MySQL
docker ps | grep mysql

# Reiniciar MySQL
docker compose -f docker-compose.mysql.yml restart
```

### Erro: Certbot não consegue obter certificado

**Causa:** DNS não está configurado ou domínio não aponta para a VPS

**Solução:**
1. Configure o DNS do domínio para apontar para `76.13.167.54`
2. Aguarde a propagação do DNS (pode levar até 24h)
3. Execute o script novamente ou rode o certbot manualmente

## 📚 Documentação Relacionada

- [`CONFIGURAR_NGINX_PHPFPM_DIRETO.md`](CONFIGURAR_NGINX_PHPFPM_DIRETO.md) - Configuração do Nginx para Laravel
- [`INSTRUCOES_RESOLVER_CONFLITO_PORTA_80.md`](INSTRUCOES_RESOLVER_CONFLITO_PORTA_80.md) - Resolver conflito de porta 80
- [`docker-compose.mysql.yml`](docker-compose.mysql.yml) - Configuração do MySQL

## 🎉 Próximos Passos

Após executar o script com sucesso:

1. **Configure o DNS** - Configure o DNS do domínio para apontar para a VPS
2. **Configure o Cloudflare** - Se usar Cloudflare, configure o proxy
3. **Teste a aplicação** - Acesse `https://api.cobrancaauto.com.br/admin/saas/dashboard`
4. **Configure email** - Configure as variáveis `MAIL_*` no .env
5. **Configure backups** - Configure backups automáticos do banco de dados
6. **Monitore os logs** - Monitore os logs do Nginx e Laravel

## 📞 Suporte

Se tiver problemas:

1. Verifique os logs: `tail -f /var/log/nginx/error.log`
2. Verifique os logs do Laravel: `tail -f storage/logs/laravel.log`
3. Verifique os logs do MySQL: `docker compose -f docker-compose.mysql.yml logs`
4. Execute o diagnóstico: `systemctl status nginx`
