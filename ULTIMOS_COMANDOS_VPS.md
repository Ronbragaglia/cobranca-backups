# ÚLTIMOS COMANDOS PARA EXECUTAR NA VPS

## 🚀 EXECUTE AGORA MESMO NA VPS

Copie e cole estes comandos na VPS, um por vez:

---

## 1. PARAR E REMOVER CONTAINERS COMPLETAMENTE

```bash
cd /var/www/cobranca-api
docker-compose -f docker-compose.prod.yml down -v
```

---

## 2. SUBIR CONTAINERS COM NOVA CONFIGURAÇÃO

```bash
docker-compose -f docker-compose.prod.yml up -d
```

---

## 3. AGUARDAR 30 SEGUNDOS

```bash
sleep 30
```

---

## 4. VERIFICAR STATUS DOS CONTAINERS

```bash
docker-compose -f docker-compose.prod.yml ps
```

**Deveria mostrar:**
```
NAME                STATUS
cobranca_app       Up
cobranca_redis       Up (healthy)
cobranca_mysql       Up (healthy)
cobranca_queue       Up
cobranca_scheduler   Up
cobranca_backup       Up
```

---

## 5. VERIFICAR SE A PORTA 9000 ESTÁ EXPOSTA

```bash
netstat -tlnp | grep 9000
```

**Deveria mostrar:**
```
tcp        0      127.0.0.1:9000            0.0.0.0:*               LISTEN
```

---

## 6. VERIFICAR LOGS DO APP

```bash
docker-compose -f docker-compose.prod.yml logs app | tail -50
```

**Deveria mostrar algo como:**
```
[04-Feb-2026 16:00:00] NOTICE: fpm is running, pid 1
```

---

## 7. TESTAR HEALTH CHECK

```bash
curl https://api.cobrancaauto.com.br/health
```

**Deveria retornar:** `{"status":"ok"}` ou similar

---

## 8. TESTAR SITE PRINCIPAL

```bash
curl https://api.cobrancaauto.com.br/
```

**Deveria retornar:** HTML ou JSON (não 502)

---

## 🔍 SE AINDA DER ERRO 502

### VERIFICAR LOGS DO NGINX (EM DENTRO DO CONTAINER)

```bash
docker-compose -f docker-compose.prod.yml logs nginx-laravel | tail -50
```

### VERIFICAR LOGS DO NGINX (NO SERVIDOR)

```bash
tail -100 /var/log/nginx/cobranca-api-error.log
```

### VERIFICAR SE O CONTAINER NGINX ESTÁ RODANDO

```bash
docker-compose -f docker-compose.prod.yml ps nginx-laravel
```

---

## 📝 RESUMO DAS MUDANÇAS

### Arquivos atualizados:

1. **[`.env`](.env:1)** - Atualizado com Redis completo
2. **[`docker-compose.prod.yml`](docker-compose.prod.yml:1)** - Porta 9000 exposta, healthchecks removidos
3. **[`nginx-laravel.conf`](nginx-laravel.conf:1)** - Configuração Nginx para Docker criada

### Comandos para executar:

```bash
# 1. Parar e remover containers
docker-compose -f docker-compose.prod.yml down -v

# 2. Subir containers
docker-compose -f docker-compose.prod.yml up -d

# 3. Aguardar
sleep 30

# 4. Verificar status
docker-compose -f docker-compose.prod.yml ps

# 5. Verificar porta
netstat -tlnp | grep 9000

# 6. Testar health check
curl https://api.cobrancaauto.com.br/health

# 7. Testar site
curl https://api.cobrancaauto.com.br/
```

---

## 🚀 RESULTADO ESPERADO

Após executar os comandos acima:

1. ✅ Todos os containers rodando
2. ✅ Porta 9000 exposta: `127.0.0.1:9000`
3. ✅ Health check funcionando: `{"status":"ok"}`
4. ✅ Site carregando: HTML/JSON (não 502)
5. ✅ Redis conectado: logs sem erros

Se tudo estiver funcionando, o site está pronto para uso!

---

**ÚLTIMA ATUALIZAÇÃO:** 2026-02-04  
**VERSÃO:** 1.0  
**STATUS:** PRONTO PARA EXECUÇÃO
