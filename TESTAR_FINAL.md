# 🚀 TESTAR FINALMENTE

## 📋 Execute estes comandos na VPS (em ordem):

### Passo 1: Verificar em qual porta o PHP-FPM está escutando
```bash
docker exec cobranca_app netstat -tlnp | grep 9000
```

**Resultado esperado:**
```
tcp        0      0 0.0.0.0:9000            0.0.0.0:*               LISTEN      1/php-fpm.conf)
```

### Passo 2: Verificar se a porta 9000 está exposta
```bash
docker port cobranca_app
```

**Resultado esperado:**
```
9000/tcp -> 127.0.0.1:9000
```

### Passo 3: Verificar os containers
```bash
docker ps
```

### Passo 4: Verificar configuração do Nginx
```bash
docker exec cobranca_nginx cat /etc/nginx/conf.d/default.conf
```

### Passo 5: Testar conexão com PHP-FPM
```bash
curl -I http://127.0.0.1:9000
```

**Resultado esperado:**
```
HTTP/1.1 200 OK
```

### Passo 6: Testar o endpoint /health
```bash
curl -i http://api.cobrancaauto.com.br/health
```

**Resultado esperado:**
```
HTTP/1.1 200 OK
{"status":"ok","app":"cobranca-auto"}
```

### Passo 7: Testar o site principal
```bash
curl -I https://api.cobrancaauto.com.br/
```

**Resultado esperado:**
```
HTTP/1.1 200 OK
```

## 📋 Se o Passo 5 falhar

Se a conexão com o PHP-FPM falhar, execute:

```bash
# Verificar se o PHP-FPM está escutando em IPv4
docker exec cobranca_app netstat -tlnp | grep 9000

# Se estiver em IPv6 (:::9000), reiniciar o PHP-FPM
docker exec cobranca_app killall php-fpm
docker exec cobranca_app php-fpm -D

# Aguardar 5 segundos
sleep 5

# Verificar novamente
docker exec cobranca_app netstat -tlnp | grep 9000

# Testar conexão
curl -I http://127.0.0.1:9000
```

## 📋 Se o Passo 7 falhar

Se o site principal não funcionar, verifique os logs:

```bash
# Verificar logs do Nginx
docker logs cobranca_nginx --tail 50

# Verificar logs do app
docker logs cobranca_app --tail 50
```

## 🎯 Resumo

1. Verificar se o PHP-FPM está escutando em IPv4: `docker exec cobranca_app netstat -tlnp | grep 9000`
2. Verificar se a porta 9000 está exposta: `docker port cobranca_app`
3. Testar conexão: `curl -I http://127.0.0.1:9000`
4. Testar site: `curl -I https://api.cobrancaauto.com.br/`

Execute os comandos acima em ordem e me envie os resultados!
