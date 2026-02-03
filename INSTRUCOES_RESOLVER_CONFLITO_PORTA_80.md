# Instruções para Resolver Conflito de Porta 80 entre Docker-Proxy e Nginx

## Problema
O Nginx não consegue iniciar porque a porta 80 está sendo usada pelo `docker-proxy`. O proxy antigo ainda está respondendo com redirect 308 para HTTPS.

## Solução

### Opção 1: Executar Script Automático (RECOMENDADO)

Execute este script na VPS (IP: 76.13.167.54):

```bash
# Copie o script para a VPS ou crie-o diretamente
nano /root/fix-nginx-docker-proxy.sh
```

Cole o conteúdo do arquivo [`scripts/fix-nginx-docker-proxy.sh`](scripts/fix-nginx-docker-proxy.sh)

Depois execute:

```bash
chmod +x /root/fix-nginx-docker-proxy.sh
/root/fix-nginx-docker-proxy.sh
```

### Opção 2: Executar Comandos Manualmente

#### Passo 1: Identificar containers usando portas 80/443

```bash
# Listar containers usando porta 80
docker ps -a --filter "publish=80"

# Listar containers usando porta 443
docker ps -a --filter "publish=443"

# Listar todos os containers
docker ps -a

# Verificar processos na porta 80
netstat -tlnp | grep :80
```

#### Passo 2: Parar e remover containers que usam porta 80/443

```bash
# Substitua <NOME_CONTAINER> pelo nome do container encontrado
docker stop <NOME_CONTAINER>
docker rm <NOME_CONTAINER>

# Exemplo comum (ajuste conforme necessário):
docker stop traefik caddy nginx-proxy
docker rm traefik caddy nginx-proxy
```

#### Passo 3: Verificar se ainda há docker-proxy rodando

```bash
# Verificar processos docker-proxy
ps aux | grep docker-proxy

# Se ainda houver, mate o processo (CUIDADO!)
# kill -9 <PID_DO_PROCESSO>
```

#### Passo 4: Iniciar o Nginx

```bash
# Testar configuração do nginx
nginx -t

# Iniciar nginx
systemctl restart nginx

# Verificar status
systemctl status nginx
```

#### Passo 5: Verificar portas

```bash
# Verificar porta 80
netstat -tlnp | grep :80

# Verificar porta 443
netstat -tlnp | grep :443
```

#### Passo 6: Testar acesso

```bash
# Testar HTTP
curl -I http://76.13.167.54

# Testar localhost
curl -I http://localhost
```

## Verificação Final

Após executar os comandos, verifique:

1. ✅ A porta 80 está sendo atendida pelo nginx (não por docker-proxy)
2. ✅ `systemctl status nginx` mostra "active (running)"
3. ✅ `netstat -tlnp | grep :80` mostra nginx/master como processo
4. ✅ `curl -I http://76.13.167.54` retorna HTTP 200 (não 308)

## Troubleshooting

### Se o nginx não iniciar:

```bash
# Verificar logs do nginx
journalctl -u nginx -n 50

# Verificar configuração
nginx -t

# Verificar se há outro processo na porta 80
lsof -i :80
```

### Se ainda houver docker-proxy:

```bash
# Listar todos os containers
docker ps -a

# Parar todos os containers (CUIDADO!)
docker stop $(docker ps -aq)

# Remover todos os containers (CUIDADO!)
docker rm $(docker ps -aq)
```

### Se precisar reiniciar o docker:

```bash
systemctl restart docker
```

## Configuração Esperada do Nginx

O arquivo `/etc/nginx/sites-available/cobranca-api` deve estar configurado para:

- Escutar na porta 80
- Fazer proxy pass para PHP-FPM na porta 8082
- Servir o Laravel em `/root/cobranca-api/public`

Exemplo de configuração:

```nginx
server {
    listen 80;
    server_name 76.13.167.54;

    root /root/cobranca-api/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:8082;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Comandos Rápidos de Diagnóstico

```bash
# Ver tudo de uma vez
echo "=== Containers ===" && docker ps -a && \
echo "=== Porta 80 ===" && netstat -tlnp | grep :80 && \
echo "=== Nginx Status ===" && systemctl status nginx --no-pager && \
echo "=== Curl Test ===" && curl -I http://localhost
```

## Próximos Passos

Após resolver o conflito:

1. Verifique se a aplicação Laravel está funcionando
2. Configure o firewall se necessário
3. Configure SSL/TLS (opcional, usando certbot)
4. Monitore os logs do nginx: `tail -f /var/log/nginx/access.log`
