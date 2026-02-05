# TESTAR SITE FINAL

## 🎉 PROGRESSO

✅ Container reconstruído com sucesso
✅ Container rodando
✅ Porta 9000 exposta

## ✅ TESTAR SITE

Execute estes comandos:

```bash
# 1. Verificar status dos containers
docker-compose -f /var/www/cobranca-api/docker-compose.prod.yml ps
```

```bash
# 2. Verificar se a porta está exposta
docker port cobranca_app
```

```bash
# 3. Testar health check
curl -I https://api.cobrancaauto.com.br/health
```

```bash
# 4. Testar site
curl -I https://api.cobrancaauto.com.br/
```

---

## 📋 O QUE ESPERAR

### Após `docker-compose -f /var/www/cobranca-api/docker-compose.prod.yml ps`:

```
NAME                STATUS
cobranca_mysql       Up (healthy)
cobranca_redis       Up (healthy)
cobranca_app       Up (healthy)
cobranca_queue       Up
cobranca_scheduler   Up
```

### Após `docker port cobranca_app`:

```
9000/tcp -> 127.0.0.1:9000
```

### Após `curl -I https://api.cobrancaauto.com.br/health`:

```
HTTP/1.1 200 OK
```

### Após `curl -I https://api.cobrancaauto.com.br/`:

```
HTTP/1.1 200 OK
```

---

## 🔍 SE AINDA NÃO FUNCIONAR

Se ainda retornar 404 ou 502, execute:

```bash
# Verificar logs do container app
docker logs cobranca_app | tail -50

# Verificar logs de erro do Nginx
tail -20 /var/log/nginx/cobranca-api-error.log

# Verificar logs de acesso do Nginx
tail -20 /var/log/nginx/cobranca-api-access.log
```

---

## 📝 RESUMO

### O que fazer:

1. Verificar status dos containers
2. Verificar se a porta está exposta
3. Testar health check
4. Testar site

---

## 🚀 COMANDOS COMPLETOS (COPIAR E COLAR)

```bash
# 1. Verificar status dos containers
docker-compose -f /var/www/cobranca-api/docker-compose.prod.yml ps

# 2. Verificar se a porta está exposta
docker port cobranca_app

# 3. Testar health check
curl -I https://api.cobrancaauto.com.br/health

# 4. Testar site
curl -I https://api.cobrancaauto.com.br/
```

---

**ÚLTIMA ATUALIZAÇÃO:** 2026-02-04  
**VERSÃO:** 1.0  
**STATUS:** PRONTO PARA EXECUÇÃO
