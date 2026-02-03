# 📋 Resumo: Finalizar Configuração da VPS

## ✅ Arquivos Criados

1. **[`scripts/finalizar-cobranca-api-vps.sh`](scripts/finalizar-cobranca-api-vps.sh)** - Script executável completo
2. **[`SCRIPT_FINALIZAR_COBRANCA_API_VPS_COMPLETO.txt`](SCRIPT_FINALIZAR_COBRANCA_API_VPS_COMPLETO.txt)** - Script em texto para copiar
3. **[`INSTRUCOES_FINALIZAR_VPS.md`](INSTRUCOES_FINALIZAR_VPS.md)** - Instruções detalhadas

## ⚙️ Configurações do Script

O script usa as seguintes configurações:

- **Caminho do projeto:** `/var/www/cobranca-api` ✅ (ATUALIZADO)
- **Domínio:** `api.cobrancaauto.com.br`
- **Email para Certbot:** `seu@email.com`

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

## 🚀 Como Executar na VPS

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

## ⚠️ IMPORTANTE: Antes de Executar

### 1. Verificar o caminho do projeto

Certifique-se de que o projeto está no caminho correto:

```bash
# Verificar se o diretório existe
ls -la /var/www/cobranca-api

# Se não existir, você precisará mover o projeto:
mv /root/cobranca-api /var/www/cobranca-api
```

### 2. Configurar o DNS

Configure o DNS do domínio `api.cobrancaauto.com.br` para apontar para o IP da VPS `76.13.167.54` **ANTES** de executar o script.

### 3. Verificar variáveis do script

Se precisar alterar as configurações, edite o script antes de executar:

```bash
nano /root/finalizar-cobranca-api-vps.sh
```

Altere as variáveis no início do script:

```bash
PROJECT_PATH="/var/www/cobranca-api"
DOMAIN="api.cobrancaauto.com.br"
EMAIL="seu@email.com"
```

## 📝 Após Executar o Script

### 1. Editar o .env

```bash
cd /var/www/cobranca-api
nano .env
```

Configure as variáveis importantes:
- `DB_PASSWORD` - Senha do MySQL (deve ser a mesma definida no docker-compose.mysql.yml)
- `APP_URL=https://api.cobrancaauto.com.br`
- `MAIL_*` - Configurações de email
- Outras variáveis necessárias para produção

### 2. Reiniciar o Laravel

```bash
cd /var/www/cobranca-api
php artisan config:clear
php artisan cache:clear
php artisan migrate:fresh --seed --force
```

### 3. Configurar o Cloudflare (se usar)

1. Adicione o domínio `api.cobrancaauto.com.br` ao Cloudflare
2. Configure o DNS para apontar para `76.13.167.54`
3. Ative o proxy (cloud laranja)
4. Configure as configurações de SSL/TLS no Cloudflare

### 4. Testar a aplicação

```bash
# Testar HTTP
curl -I http://api.cobrancaauto.com.br

# Testar HTTPS
curl -I https://api.cobrancaauto.com.br

# Testar no navegador
# Acesse: https://api.cobrancaauto.com.br/admin/saas/dashboard
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
cd /var/www/cobranca-api
docker compose -f docker-compose.mysql.yml restart
```

### Erro: Certbot não consegue obter certificado

**Causa:** DNS não está configurado ou domínio não aponta para a VPS

**Solução:**
1. Configure o DNS do domínio para apontar para `76.13.167.54`
2. Aguarde a propagação do DNS (pode levar até 24h)
3. Execute o script novamente ou rode o certbot manualmente

## 📚 Documentação Relacionada

- [`INSTRUCOES_FINALIZAR_VPS.md`](INSTRUCOES_FINALIZAR_VPS.md) - Instruções detalhadas
- [`CONFIGURAR_NGINX_PHPFPM_DIRETO.md`](CONFIGURAR_NGINX_PHPFPM_DIRETO.md) - Configuração do Nginx
- [`docker-compose.mysql.yml`](docker-compose.mysql.yml) - Configuração do MySQL

## 🎉 Próximos Passos

Após executar o script com sucesso:

1. ✅ Configure o DNS do domínio para apontar para a VPS
2. ✅ Configure o Cloudflare (se usar) para apontar para o domínio
3. ✅ Teste a aplicação em `https://api.cobrancaauto.com.br/admin/saas/dashboard`
4. ✅ Configure as variáveis `MAIL_*` no .env
5. ✅ Configure backups automáticos do banco de dados
6. ✅ Monitore os logs do Nginx e Laravel

## 📞 Suporte

Se tiver problemas:

1. Verifique os logs: `tail -f /var/log/nginx/error.log`
2. Verifique os logs do Laravel: `tail -f storage/logs/laravel.log`
3. Verifique os logs do MySQL: `docker compose -f docker-compose.mysql.yml logs`
4. Execute o diagnóstico: `systemctl status nginx`

## ✅ Checklist Antes de Executar

- [ ] Projeto está em `/var/www/cobranca-api` (ou mova de `/root/cobranca-api`)
- [ ] DNS do domínio `api.cobrancaauto.com.br` está configurado para `76.13.167.54`
- [ ] Variáveis do script estão corretas (PROJECT_PATH, DOMAIN, EMAIL)
- [ ] Você tem acesso root à VPS
- [ ] Docker está instalado e funcionando
- [ ] Nginx está instalado

Quando todos os itens estiverem marcados, você pode executar o script!
