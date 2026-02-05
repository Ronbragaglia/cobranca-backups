# 📚 DOCUMENTO TÉCNICO: FIX 502 BAD GATEWAY

## 🔍 ANÁLISE DO PROBLEMA

### Sintoma Principal
```
curl phpinfo.php = 11 bytes de texto cru
```

Isso indica que o NGINX está servindo o arquivo PHP como texto estático, em vez de processá-lo através do PHP-FPM.

### Causa Raiz
O NGINX não está configurado corretamente para passar requisições PHP para o PHP-FPM via socket UNIX.

---

## 🏗️ ARQUITETURA NGINX + PHP-FPM

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Browser   │────▶│   NGINX     │────▶│  PHP-FPM    │
│             │     │  (Porta 80) │     │  (Socket)   │
└─────────────┘     └─────────────┘     └─────────────┘
                          │                      │
                          │                      ▼
                          │              ┌─────────────┐
                          │              │  Laravel    │
                          │              │  App        │
                          │              └─────────────┘
                          │                      │
                          └──────────────────────┘
                                 MySQL
```

### Fluxo Correto
1. Browser → NGINX (porta 80)
2. NGINX detecta arquivo `.php`
3. NGINX passa para PHP-FPM via socket
4. PHP-FPM processa o código
5. PHP-FPM retorna HTML para NGINX
6. NGINX envia para browser

### Fluxo Incorreto (502)
1. Browser → NGINX (porta 80)
2. NGINX detecta arquivo `.php`
3. ❌ NGINX não consegue conectar ao PHP-FPM
4. ❌ NGINX retorna 502 Bad Gateway

---

## 🔧 CONFIGURAÇÃO NGINX CORRETA

### Bloco PHP Essencial
```nginx
location ~ \.php$ {
    # Incluir configuração padrão FastCGI
    include snippets/fastcgi-php.conf;
    
    # Passar para socket UNIX do PHP-FPM
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    
    # Definir arquivo de script
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    
    # Incluir parâmetros FastCGI padrão
    include fastcgi_params;
    
    # Timeouts importantes para Laravel
    fastcgi_read_timeout 300;
    fastcgi_connect_timeout 300;
    fastcgi_send_timeout 300;
}
```

### Explicação dos Parâmetros

| Parâmetro | Valor | Descrição |
|-----------|-------|-----------|
| `snippets/fastcgi-php.conf` | - | Configuração padrão Debian/Ubuntu |
| `fastcgi_pass` | `unix:/var/run/php/php8.2-fpm.sock` | Socket UNIX do PHP-FPM |
| `SCRIPT_FILENAME` | `$document_root$fastcgi_script_name` | Caminho completo do arquivo PHP |
| `fastcgi_params` | - | Parâmetros CGI padrão |
| `fastcgi_read_timeout` | 300 | Timeout de leitura (5 min) |
| `fastcgi_connect_timeout` | 300 | Timeout de conexão (5 min) |
| `fastcgi_send_timeout` | 300 | Timeout de envio (5 min) |

---

## 🔌 SOCKET UNIX vs TCP

### Socket UNIX (Recomendado)
```nginx
fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
```
- ✅ Mais rápido (comunicação local)
- ✅ Menor latência
- ✅ Segurança adicional (permissões de arquivo)
- ✅ Padrão Debian/Ubuntu

### Socket TCP (Alternativa)
```nginx
fastcgi_pass 127.0.0.1:9000;
```
- ⚠️ Mais lento (overhead TCP)
- ⚠️ Reconfiguração PHP-FPM necessária
- ✅ Funciona se socket UNIX falhar

---

## 🔍 DIAGNÓSTICO DETALHADO

### 1. Verificar Socket PHP-FPM
```bash
# Socket existe?
ls -la /var/run/php/php8.2-fpm.sock

# Saída esperada:
# srw-rw---- 1 www-data www-data 0 Feb  3 14:00 /var/run/php/php8.2-fpm.sock

# Se não existir:
systemctl restart php8.2-fpm
```

### 2. Verificar Permissões do Socket
```bash
# Verificar permissões
stat /var/run/php/php8.2-fpm.sock

# NGINX precisa ter permissão de leitura/escrita
# Usuário NGINX: www-data (padrão)
# Usuário PHP-FPM: www-data (padrão)

# Se permissões erradas:
chmod 666 /var/run/php/php8.2-fpm.sock
```

### 3. Verificar Usuários
```bash
# Usuário NGINX
grep "^user" /etc/nginx/nginx.conf

# Usuário PHP-FPM
grep -E "^(user|group)" /etc/php/8.2/fpm/pool.d/www.conf | grep -v ';'

# Ambos devem ser www-data
```

### 4. Verificar Configuração NGINX
```bash
# Verificar se bloco PHP existe
cat /etc/nginx/sites-available/cobranca-api | grep -A 10 "location ~ \.php$"

# Testar sintaxe
nginx -t

# Recarregar se OK
nginx -s reload
```

### 5. Verificar Logs NGINX
```bash
# Últimos erros
tail -50 /var/log/nginx/error.log

# Erros comuns:
# - "connect() to unix:/var/run/php/php8.2-fpm.sock failed"
# - "Connection refused"
# - "Permission denied"
```

### 6. Verificar Logs PHP-FPM
```bash
# Últimos erros
tail -50 /var/log/php8.2-fpm.log

# Erros comuns:
# - "pool www not found"
# - "unable to bind listening socket"
# - "address already in use"
```

---

## 🐛 ERROS COMUNS E SOLUÇÕES

### Erro 1: "connect() to unix:/var/run/php/php8.2-fpm.sock failed (2: No such file or directory)"

**Causa:** PHP-FPM não está rodando ou socket não foi criado

**Solução:**
```bash
systemctl restart php8.2-fpm
systemctl status php8.2-fpm
```

### Erro 2: "connect() to unix:/var/run/php/php8.2-fpm.sock failed (13: Permission denied)"

**Causa:** NGINX não tem permissão para acessar o socket

**Solução:**
```bash
chmod 666 /var/run/php/php8.2-fpm.sock
# Ou corrigir usuários para serem iguais
```

### Erro 3: "upstream timed out (110: Connection timed out)"

**Causa:** PHP-FPM demorando muito ou travado

**Solução:**
```bash
# Aumentar timeouts em nginx.conf
fastcgi_read_timeout 600;
fastcgi_connect_timeout 600;
fastcgi_send_timeout 600;

# Ou reiniciar PHP-FPM
systemctl restart php8.2-fpm
```

### Erro 4: "recv() failed (104: Connection reset by peer)"

**Causa:** PHP-FPM caiu durante processamento

**Solução:**
```bash
# Verificar memória disponível
free -h

# Aumentar memory_limit no PHP
nano /etc/php/8.2/fpm/php.ini
# memory_limit = 256M

# Reiniciar PHP-FPM
systemctl restart php8.2-fpm
```

---

## 📊 MONITORAMENTO

### Verificar Conexões Ativas
```bash
# Conexões PHP-FPM
ss -x | grep php

# Conexões NGINX
ss -tlnp | grep :80
```

### Verificar Processos
```bash
# Processos PHP-FPM
ps aux | grep php-fpm

# Processos NGINX
ps aux | grep nginx
```

### Verificar Recursos
```bash
# Uso de memória
free -h

# Uso de CPU
top -bn1 | head -20

# Disco
df -h
```

---

## 🔒 SEGURANÇA

### Bloquear Acesso a Arquivos Sensíveis
```nginx
# Negar acesso a arquivos ocultos
location ~ /\. {
    deny all;
    access_log off;
    log_not_found off;
}

# Negar acesso a arquivos .env, .git, etc.
location ~ /\.(?:git|svn|hg|bzr|env) {
    deny all;
}

# Negar acesso a logs
location ~ \.log$ {
    deny all;
}
```

### Rate Limiting
```nginx
# Limitar requisições por IP
limit_req_zone $binary_remote_addr zone=one:10m rate=10r/s;

location ~ \.php$ {
    limit_req zone=one burst=20 nodelay;
    # ... resto da configuração
}
```

---

## 🚀 OTIMIZAÇÃO

### PHP-FPM Pool Configuration
```ini
# /etc/php/8.2/fpm/pool.d/www.conf

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### NGINX Worker Processes
```nginx
# /etc/nginx/nginx.conf

worker_processes auto;
worker_connections 1024;
multi_accept on;
use epoll;
```

---

## 📝 CHECKLIST DE VERIFICAÇÃO

- [ ] PHP-FPM está rodando (`systemctl status php8.2-fpm`)
- [ ] NGINX está rodando (`systemctl status nginx`)
- [ ] Socket PHP-FPM existe (`ls -la /var/run/php/php8.2-fpm.sock`)
- [ ] Permissões do socket estão corretas
- [ ] Usuário NGINX = Usuário PHP-FPM
- [ ] Configuração NGINX tem bloco `location ~ \.php$`
- [ ] `fastcgi_pass` aponta para socket correto
- [ ] `nginx -t` não retorna erros
- [ ] Logs NGINX não mostram erros
- [ ] Logs PHP-FPM não mostram erros
- [ ] Site responde HTTP 200
- [ ] Arquivos PHP são processados corretamente

---

## 🎯 CONCLUSÃO

O erro 502 Bad Gateway é quase sempre causado por:
1. PHP-FPM não rodando
2. Socket PHP-FPM não acessível
3. Configuração NGINX incorreta
4. Permissões erradas

O script [`fix-502-nginx-php-urgente.sh`](../scripts/fix-502-nginx-php-urgente.sh) resolve todos esses problemas automaticamente.

---

**💚 Site funcionando = Cliente feliz = 💸**
