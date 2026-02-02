# COMANDOS EXATOS - DEPLOY LOCAL → VPS HOSTINGER

**VPS:** 76.13.167.54 (Ubuntu 22.04, root)  
**Objetivo:** Transferir projeto e testar docker-compose.dev.yml

---

## 📋 SEQUÊNCIA DE 10 COMANDOS

### COMANDO 1 - TESTAR CONEXÃO SSH
**Terminal:** LOCAL  
**Comando:**
```bash
ssh root@76.13.167.54 "echo 'Conexão SSH OK' && exit"
```
**O que faz:** Testa se consegue conectar na VPS via SSH e sai imediatamente.

---

### COMANDO 2 - INSTALAR DOCKER NA VPS
**Terminal:** LOCAL (via SSH)  
**Comando:**
```bash
ssh root@76.13.167.54 "apt-get update && apt-get install -y docker.io docker-compose && systemctl enable docker && systemctl start docker && docker --version && docker-compose --version"
```
**O que faz:** Atualiza pacotes, instala Docker e Docker Compose, habilita e inicia o serviço Docker.

---

### COMANDO 3 - CRIAR DIRETÓRIO DO PROJETO NA VPS
**Terminal:** LOCAL (via SSH)  
**Comando:**
```bash
ssh root@76.13.167.54 "mkdir -p /var/www/cobranca-auto && ls -la /var/www/"
```
**O que faz:** Cria o diretório /var/www/cobranca-auto na VPS e lista para confirmar.

---

### COMANDO 4 - COMPACTAR PROJETO LOCAL
**Terminal:** LOCAL  
**Comando:**
```bash
tar -czf cobranca-api.tar.gz --exclude='node_modules' --exclude='vendor' --exclude='.git' --exclude='storage/logs/*' --exclude='storage/framework/cache/*' --exclude='storage/framework/sessions/*' --exclude='storage/framework/views/*' .
```
**O que faz:** Cria arquivo tar.gz do projeto excluindo diretórios desnecessários.

---

### COMANDO 5 - TRANSFERIR ARQUIVO PARA VPS
**Terminal:** LOCAL  
**Comando:**
```bash
scp cobranca-api.tar.gz root@76.13.167.54:/var/www/cobranca-api.tar.gz
```
**O que faz:** Transfere o arquivo compactado via SCP para a VPS.

---

### COMANDO 6 - EXTRAIR PROJETO NA VPS
**Terminal:** LOCAL (via SSH)  
**Comando:**
```bash
ssh root@76.13.167.54 "cd /var/www && tar -xzf cobranca-api.tar.gz -C cobranca-auto && rm cobranca-api.tar.gz && ls -la cobranca-auto/"
```
**O que faz:** Extrai o projeto no diretório correto, remove o tar.gz e lista arquivos.

---

### COMANDO 7 - CONFIGURAR FIREWALL NA VPS
**Terminal:** LOCAL (via SSH)  
**Comando:**
```bash
ssh root@76.13.167.54 "ufw allow 80/tcp && ufw allow 8080/tcp && ufw allow 22/tcp && ufw --force enable && ufw status"
```
**O que faz:** Libera portas 80, 8080 e 22 no firewall UFW e habilita.

---

### COMANDO 8 - RODAR DOCKER COMPOSE DEV
**Terminal:** LOCAL (via SSH)  
**Comando:**
```bash
ssh root@76.13.167.54 "cd /var/www/cobranca-auto && docker-compose -f docker-compose.dev.yml up -d --build"
```
**O que faz:** Inicia todos os containers do docker-compose.dev.yml em modo detached.

---

### COMANDO 9 - TESTAR APLICAÇÃO
**Terminal:** LOCAL (via SSH)  
**Comando:**
```bash
ssh root@76.13.167.54 "sleep 15 && docker ps && curl -I http://localhost:8000 && echo '---' && curl -I http://localhost:8080"
```
**O que faz:** Aguarda 15s, lista containers em execução e testa HTTP nas portas 8000 e 8080.

---

### COMANDO 10 - VER LOGS E VALIDAR
**Terminal:** LOCAL (via SSH)  
**Comando:**
```bash
ssh root@76.13.167.54 "cd /var/www/cobranca-auto && docker-compose -f docker-compose.dev.yml logs --tail=50 app && echo '---' && docker-compose -f docker-compose.dev.yml logs --tail=20 mysql"
```
**O que faz:** Mostra os últimos 50 logs do container app e últimos 20 do mysql.

---

## ✅ CHECKLIST DE VALIDAÇÃO

### 1. O que deve aparecer em `docker ps`:
```
CONTAINER ID   IMAGE                      COMMAND                  CREATED         STATUS         PORTS                                      NAMES
xxxxxxxxxxxx   cobranca-auto_app          "docker-php-entrypoi…"   X minutes ago   Up X minutes   9000/tcp                                   cobranca_app
xxxxxxxxxxxx   nginx:alpine               "/docker-entrypoint.…"   X minutes ago   Up X minutes   0.0.0.0:8000->80/tcp, 0.0.0.0:8080->80/tcp   cobranca_web
xxxxxxxxxxxx   mysql:8.0                  "docker-entrypoint.s…"   X minutes ago   Up X minutes   0.0.0.0:3306->3306/tcp, 33060/tcp          cobranca_mysql
xxxxxxxxxxxx   phpmyadmin/phpmyadmin      "/docker-entrypoint.s…"   X minutes ago   Up X minutes   0.0.0.0:8080->80/tcp                       cobranca_phpmyadmin
xxxxxxxxxxxx   cobranca-auto_queue        "php artisan queue:wo…"   X minutes ago   Up X minutes   9000/tcp                                   cobranca_queue
xxxxxxxxxxxx   cobranca-auto_scheduler    "sh -c 'while true; d…"   X minutes ago   Up X minutes   9000/tcp                                   cobranca_scheduler
```

### 2. Resposta esperada do curl (porta 8000):
```
HTTP/1.1 200 OK
Server: nginx
Content-Type: text/html; charset=UTF-8
...
```
Ou redirecionamento 302 para login (normal em Laravel).

### 3. Resposta esperada do curl (porta 8080):
```
HTTP/1.1 200 OK
Server: nginx
Content-Type: text/html; charset=UTF-8
...
```
Deve mostrar a interface do phpMyAdmin.

### 4. Logs que indicam SUCESSO:

**Container app (Laravel):**
```
[INFO] Server started on port 9000
[INFO] Laravel framework started
[INFO] Database connection established
```

**Container mysql:**
```
[Note] mysqld: ready for connections.
[Note] Version: '8.0.xx'  socket: '/var/run/mysqld/mysqld.sock'  port: 3306
```

**Container web (Nginx):**
```
[INFO] start worker processes
[INFO] start worker process
```

---

## 🔧 TROUBLESHOOTING COMUM

### 1. "Permission denied" no SCP
**Causa:** Chave SSH não configurada ou senha incorreta.  
**Solução:**
```bash
# Teste conexão primeiro
ssh root@76.13.167.54

# Se pedir senha, use:
scp cobranca-api.tar.gz root@76.13.167.54:/var/www/cobranca-api.tar.gz
# (será solicitada a senha)
```

### 2. "Port already in use"
**Causa:** Outro processo usando a porta 8000 ou 8080.  
**Solução:**
```bash
ssh root@76.13.167.54 "lsof -i :8000 -i :8080"
# Se tiver processo, mate:
ssh root@76.13.167.54 "kill -9 <PID>"
# Ou mude as portas no docker-compose.dev.yml
```

### 3. "No such image" Docker
**Causa:** Imagens não foram baixadas ou build falhou.  
**Solução:**
```bash
ssh root@76.13.167.54 "cd /var/www/cobranca-auto && docker-compose -f docker-compose.dev.yml pull && docker-compose -f docker-compose.dev.yml build --no-cache"
```

### 4. Container não inicia (Exit status)
**Causa:** Erro de configuração ou dependência.  
**Solução:**
```bash
# Ver logs específicos
ssh root@76.13.167.54 "cd /var/www/cobranca-auto && docker-compose -f docker-compose.dev.yml logs app"
ssh root@76.13.167.54 "cd /var/www/cobranca-auto && docker-compose -f docker-compose.dev.yml logs mysql"
```

### 5. Erro de permissão em volumes
**Causa:** Permissões incorretas no diretório.  
**Solução:**
```bash
ssh root@76.13.167.54 "cd /var/www/cobranca-auto && chmod -R 755 . && chown -R root:root ."
```

### 6. Firewall bloqueando acesso externo
**Causa:** UFW ou firewall do Hostinger bloqueando.  
**Solução:**
```bash
ssh root@76.13.167.54 "ufw status && ufw allow 8000/tcp && ufw allow 8080/tcp && ufw reload"
```

### 7. MySQL não conecta
**Causa:** Container mysql ainda não está pronto.  
**Solução:**
```bash
# Aguarde mais tempo ou verifique healthcheck
ssh root@76.13.167.54 "docker ps | grep mysql && docker logs cobranca_mysql --tail=20"
```

---

## 🧹 COMANDOS DE CLEANUP (SE DER ERRO)

### Parar todos os containers:
```bash
ssh root@76.13.167.54 "cd /var/www/cobranca-auto && docker-compose -f docker-compose.dev.yml down"
```

### Remover tudo (containers, volumes, imagens):
```bash
ssh root@76.13.167.54 "cd /var/www/cobranca-auto && docker-compose -f docker-compose.dev.yml down -v --rmi all"
```

### Remover projeto da VPS:
```bash
ssh root@76.13.167.54 "rm -rf /var/www/cobranca-auto"
```

---

## 🌐 ACESSAR APLICAÇÃO APÓS DEPLOY

**Aplicação Laravel:** http://76.13.167.54:8000  
**phpMyAdmin:** http://76.13.167.54:8080  
- Usuário: `root`  
- Senha: `root`

---

## 📊 COMANDOS ÚTEIS DE MONITORAMENTO

### Ver status dos containers:
```bash
ssh root@76.13.167.54 "cd /var/www/cobranca-auto && docker-compose -f docker-compose.dev.yml ps"
```

### Ver logs em tempo real:
```bash
ssh root@76.13.167.54 "cd /var/www/cobranca-auto && docker-compose -f docker-compose.dev.yml logs -f app"
```

### Reiniciar serviço específico:
```bash
ssh root@76.13.167.54 "cd /var/www/cobranca-auto && docker-compose -f docker-compose.dev.yml restart app"
```

### Ver uso de recursos:
```bash
ssh root@76.13.167.54 "docker stats"
```

---

## ⚠️ NOTAS IMPORTANTES

1. **Primeira execução:** O build das imagens pode levar 5-10 minutos
2. **Espere 30s** após o `up -d` antes de testar com curl
3. **Se der erro no build**, verifique se o Dockerfile está correto
4. **Porta 8000** é exposta para acesso externo (Nginx)
5. **Porta 8080** é exposta para phpMyAdmin
6. **Porta 3306** é exposta para MySQL (não use em produção)
7. **Armazenamento:** Os dados do MySQL persistem em volume docker

---

## 🎯 PRÓXIMOS PASSOS APÓS SUCESSO

1. Configurar domínio no Hostinger
2. Configurar SSL (Let's Encrypt)
3. Migrar para docker-compose.prod.yml
4. Configurar backup automático
5. Configurar monitoramento
6. Remover portas expostas desnecessárias

---

**Gerado em:** 2026-01-31  
**Versão:** 1.0
