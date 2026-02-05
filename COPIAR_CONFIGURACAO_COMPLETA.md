# COPIAR CONFIGURAÇÃO COMPLETA DO NGINX

## 🚨 PROBLEMA

O arquivo de configuração do Nginx está incompleto.

## ✅ SOLUÇÃO: COPIAR CONFIGURAÇÃO COMPLETA

### PASSO 1: Abrir o arquivo

```bash
nano /etc/nginx/sites-available/cobranca-api
```

### PASSO 2: Apagar todo o conteúdo

Pressione `Ctrl+K` repetidamente até apagar tudo, ou use:
- Pressione `Alt+Shift+6` para marcar tudo
- Pressione `Ctrl+K` para apagar

### PASSO 3: Copiar o conteúdo completo

Abra outro terminal e execute:

```bash
cat /var/www/cobranca-api/nginx-config-completo.txt
```

Ou use o VSCode para abrir o arquivo [`nginx-config-completo.txt`](nginx-config-completo.txt:1) e copiar todo o conteúdo.

### PASSO 4: Colar o conteúdo completo

No nano, pressione `Ctrl+Shift+V` para colar, ou clique com o botão direito do mouse e selecione "Colar".

### PASSO 5: Salvar e sair

- Pressione `Ctrl+O` (letra O)
- Pressione `Enter`
- Pressione `Ctrl+X`

### PASSO 6: Testar configuração

```bash
nginx -t
```

**Deveria mostrar:**
```
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

### PASSO 7: Recarregar Nginx

```bash
systemctl reload nginx
```

### PASSO 8: Verificar status

```bash
systemctl status nginx
```

**Deveria mostrar:**
```
Active: active (running)
```

### PASSO 9: Testar health check

```bash
curl https://api.cobrancaauto.com.br/health
```

### PASSO 10: Testar site

```bash
curl https://api.cobrancaauto.com.br/
```

---

## 📋 O QUE ESPERAR

### Após `nginx -t`:
```
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

### Após `systemctl status nginx`:
```
Active: active (running)
```

### Após `curl https://api.cobrancaauto.com.br/health`:
```
{"status":"ok"}
```

### Após `curl https://api.cobrancaauto.com.br/`:
HTML ou JSON (não 502)

---

## 📝 RESUMO

### O que fazer:

1. Abrir o arquivo de configuração do Nginx
2. Apagar todo o conteúdo
3. Copiar o conteúdo completo do arquivo [`nginx-config-completo.txt`](nginx-config-completo.txt:1)
4. Colar o conteúdo completo
5. Salvar e sair
6. Testar configuração
7. Recarregar Nginx
8. Testar site

---

## 🚀 COMANDOS COMPLETOS (COPIAR E COLAR)

```bash
# 1. Abrir o arquivo
nano /etc/nginx/sites-available/cobranca-api

# 2. Apagar todo o conteúdo (Ctrl+K repetidamente)

# 3. Em outro terminal, copiar o conteúdo
cat /var/www/cobranca-api/nginx-config-completo.txt

# 4. No nano, colar o conteúdo (Ctrl+Shift+V ou clique com o botão direito)

# 5. Salvar: Ctrl+O, Enter, Ctrl+X

# 6. Testar configuração
nginx -t

# 7. Recarregar Nginx
systemctl reload nginx

# 8. Verificar status
systemctl status nginx

# 9. Testar health check
curl https://api.cobrancaauto.com.br/health

# 10. Testar site
curl https://api.cobrancaauto.com.br/
```

---

## 📋 ARQUIVOS CRIADOS

1. [`nginx-config-completo.txt`](nginx-config-completo.txt:1) - Configuração completa do Nginx
2. [`COPIAR_CONFIGURACAO_COMPLETA.md`](COPIAR_CONFIGURACAO_COMPLETA.md:1) - Instruções para copiar configuração completa

---

**ÚLTIMA ATUALIZAÇÃO:** 2026-02-04  
**VERSÃO:** 1.0  
**STATUS:** PRONTO PARA EXECUÇÃO
