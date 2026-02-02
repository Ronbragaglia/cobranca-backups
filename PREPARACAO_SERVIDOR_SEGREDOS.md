# Preparação do Servidor para Receber Segredos - CobrançaAuto SaaS

⚠️ **IMPORTANTE**: Execute estas instruções ANTES de configurar os segredos no servidor.

---

## 1. Criação de Diretório Seguro

### 1.1 Criar Diretório de Configuração

```bash
# SSH na VPS
ssh root@76.13.167.54

# Criar diretório para arquivos de configuração
mkdir -p /opt/cobranca-auto

# Verificar criação
ls -la /opt/cobranca-auto
```

### 1.2 Criar Arquivo .env de Produção

```bash
# Criar arquivo .env vazio
touch /opt/cobranca-auto/.env

# Verificar criação
ls -la /opt/cobranca-auto/.env
```

---

## 2. Configuração de Permissões Seguras

### 2.1 Definir Permissões do Arquivo

```bash
# Definir permissões restritivas (apenas leitura/escrita para o dono)
chmod 600 /opt/cobranca-auto/.env

# Definir dono como root
chown root:root /opt/cobranca-auto/.env

# Verificar permissões (deve ser -rw-------)
ls -la /opt/cobranca-auto/.env
```

### 2.2 Verificar Permissões

```bash
# O resultado deve ser:
# -rw------- 1 root root ... .env

# Isso significa:
# - rw-: Leitura e escrita apenas para o dono
# - ---: Nenhuma permissão para grupo ou outros
```

---

## 3. Prevenção de Vazamento de Segredos

### 3.1 Garantir que Scripts Não Imprimem Variáveis

```bash
# Verificar scripts que podem imprimir variáveis de ambiente
grep -r "echo.*\$" /opt/cobranca-auto/ 2>/dev/null || echo "Nenhum script imprime variáveis"

# Se encontrar scripts, remova ou modifique-os para não imprimir variáveis sensíveis
```

### 3.2 Configurar Histórico de Comandos

```bash
# Desabilitar histórico para a sessão atual
unset HISTFILE

# Ou configurar para não salvar comandos com senhas
export HISTCONTROL=ignorespace
export HISTIGNORE="*password*:*secret*:*token*"
```

### 3.3 Verificar Logs do Sistema

```bash
# Verificar logs do Docker (não devem conter segredos)
docker logs cobranca_app 2>&1 | grep -i "password\|secret\|token" || echo "Logs limpos"

# Verificar logs do sistema
journalctl -u docker -n 100 --no-pager | grep -i "password\|secret\|token" || echo "Logs limpos"
```

---

## 4. Configuração do Docker/Compose

### 4.1 Método Recomendado: env_file

Editar o arquivo `docker-compose.yml` na VPS:

```yaml
version: '3.8'

services:
  app:
    build: .
    container_name: cobranca_app
    restart: unless-stopped
    env_file:
      - /opt/cobranca-auto/.env
    environment:
      APP_ENV: production
      DB_CONNECTION: mysql
      DB_HOST: mysql
      DB_PORT: 3306
      DB_DATABASE: cobranca
      DB_USERNAME: cobranca_user
      # Estas variáveis serão lidas do arquivo .env
      CLOUDFLARE_API_TOKEN: ${CLOUDFLARE_API_TOKEN}
      DB_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MAIL_PASSWORD: ${MAIL_PASSWORD}
    volumes:
      - ./storage:/var/www/storage
    depends_on:
      mysql:
        condition: service_healthy
    networks:
      - cobranca_network

  mysql:
    image: mysql:8.0
    container_name: cobranca_mysql
    restart: unless-stopped
    env_file:
      - /opt/cobranca-auto/.env
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: cobranca
      MYSQL_USER: cobranca_user
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - cobranca_network
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${MYSQL_ROOT_PASSWORD}"]
      interval: 10s
      timeout: 5s
      retries: 5
```

### 4.2 Método Alternativo: Variáveis de Ambiente

Se preferir usar variáveis de ambiente em vez de env_file:

```yaml
version: '3.8'

services:
  app:
    build: .
    container_name: cobranca_app
    restart: unless-stopped
    environment:
      APP_ENV: production
      DB_CONNECTION: mysql
      DB_HOST: mysql
      DB_PORT: 3306
      DB_DATABASE: cobranca
      DB_USERNAME: cobranca_user
      # Variáveis de ambiente (definidas antes do docker-compose up)
      CLOUDFLARE_API_TOKEN: ${CLOUDFLARE_API_TOKEN}
      DB_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MAIL_PASSWORD: ${MAIL_PASSWORD}
    volumes:
      - ./storage:/var/www/storage
    depends_on:
      mysql:
        condition: service_healthy
    networks:
      - cobranca_network
```

---

## 5. Validação de Segurança

### 5.1 Verificar Arquivo .env

```bash
# Verificar que o arquivo existe
test -f /opt/cobranca-auto/.env && echo "✅ Arquivo .env existe"

# Verificar permissões
stat -c "%a" /opt/cobranca-auto/.env

# Resultado esperado: 600 (apenas leitura/escrita para o dono)
```

### 5.2 Testar Leitura do Arquivo

```bash
# Testar que apenas root pode ler o arquivo
sudo -u root cat /opt/cobranca-auto/.env && echo "✅ Root pode ler"

# Testar que outros usuários NÃO podem ler
sudo -u nobody cat /opt/cobranca-auto/.env 2>&1 || echo "✅ Outros usuários não podem ler"
```

### 5.3 Verificar Variáveis de Ambiente

```bash
# Verificar que as variáveis não estão expostas no ambiente do sistema
env | grep -E "CLOUDFLARE_API_TOKEN|DB_PASSWORD|MYSQL_ROOT_PASSWORD|MAIL_PASSWORD" || echo "✅ Variáveis não expostas no sistema"
```

---

## 6. Instruções de Preenchimento

### 6.1 Variáveis para Preencher

Após criar o arquivo `/opt/cobranca-auto/.env`, preencha as seguintes variáveis:

```bash
# Cloudflare
CLOUDFLARE_API_TOKEN=COPIE_O_NOVO_TOKEN_AQUI

# Banco de Dados
DB_PASSWORD=COPIE_A_NOVA_SENHA_DB_AQUI
MYSQL_ROOT_PASSWORD=COPIE_A_NOVA_SENHA_ROOT_AQUI

# Email (Resend)
MAIL_PASSWORD=COPIE_A_NOVA_CHAVE_RESEND_AQUI
```

### 6.2 Como Preencher

```bash
# Editar o arquivo
nano /opt/cobranca-auto/.env

# Colar as variáveis
# Salvar: Ctrl+O
# Sair: Ctrl+X

# Verificar conteúdo
cat /opt/cobranca-auto/.env
```

---

## 7. Atualização do Docker/Compose

### 7.1 Parar Containers Atuais

```bash
# Navegar para o diretório do projeto
cd /opt/app

# Parar containers
docker-compose down

# Verificar que pararam
docker-compose ps
```

### 7.2 Atualizar docker-compose.yml

```bash
# Editar docker-compose.yml
nano docker-compose.yml

# Adicionar env_file ou environment (conforme método escolhido)
# Salvar: Ctrl+O
# Sair: Ctrl+X
```

### 7.3 Iniciar Containers com Novos Segredos

```bash
# Iniciar containers
docker-compose up -d

# Verificar que iniciaram
docker-compose ps

# Verificar logs
docker-compose logs -f
```

---

## 8. Validação Final

### 8.1 Testar Aplicação

```bash
# Testar que a aplicação está acessível
curl -I https://cobrancaauto.com.br

# Testar conexão com o banco
docker exec cobranca_app php artisan tinker
>>> DB::connection()->getPdo();
=> PDO { ... }

# Testar envio de email
docker exec cobranca_app php artisan mail:test seu-email@exemplo.com
```

### 8.2 Verificar Logs

```bash
# Verificar logs da aplicação
docker-compose logs -f app

# Verificar logs do MySQL
docker-compose logs -f mysql

# Verificar logs do sistema
journalctl -u docker -f
```

---

## 9. Checklist de Validação

Antes de considerar o servidor pronto, confirme:

- [ ] Diretório `/opt/cobranca-auto/` criado
- [ ] Arquivo `.env` criado em `/opt/cobranca-auto/`
- [ ] Permissões do arquivo definidas como `600`
- [ ] Dono do arquivo definido como `root:root`
- [ ] Nenhum script imprime variáveis sensíveis
- [ ] Logs do sistema não contêm segredos
- [ ] Variáveis de ambiente não expostas no sistema
- [ ] docker-compose.yml atualizado para usar `env_file` ou `environment`
- [ ] Containers parados e reiniciados
- [ ] Aplicação acessível e funcional
- [ ] Logs sem erros críticos

---

## 10. Hardening Adicional (Opcional)

### 10.1 Configurar Firewall

```bash
# Permitir apenas portas necessárias
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 2222/tcp  # Se usar porta SSH personalizada
ufw deny 3306/tcp  # Bloquear MySQL externamente
ufw deny 8080/tcp  # Bloquear phpMyAdmin externamente
ufw reload
```

### 10.2 Configurar Logrotate

```bash
# Configurar rotação de logs
cat > /etc/logrotate.d/cobranca-auto << 'EOF'
/opt/cobranca-auto/*.log {
    daily
    rotate 7
    compress
    missingok
    notifempty
    create 0640 root root
}
EOF

# Testar configuração
logrotate -d /etc/logrotate.d/cobranca-auto
```

### 10.3 Configurar Monitoramento

```bash
# Monitorar uso de recursos
htop

# Monitorar containers
docker stats

# Monitorar logs
docker-compose logs -f
```

---

## Resumo

✅ **Diretório seguro criado**: `/opt/cobranca-auto/`
✅ **Arquivo .env preparado**: Com permissões `600` e dono `root:root`
✅ **Docker/Compose configurado**: Para usar segredos do arquivo .env
✅ **Validações implementadas**: Para prevenir vazamento de segredos
✅ **Pronto para receber segredos**: Após o usuário completar a rotação

---

## Documentação de Referência

- Checklist de rotação: [`CHECKLIST_ROTACAO_SEGREDOS.md`](CHECKLIST_ROTACAO_SEGREDOS.md)
- Instruções detalhadas: [`INSTRUCOES_ROTACAO_SEGREDO.md`](INSTRUCOES_ROTACAO_SEGREDO.md)
- Docker env_file: https://docs.docker.com/compose/env-file/
- Permissões Linux: https://linux.die.net/ManipulatingFiles/

---

**Gerado em**: 2025-02-02
**Próxima revisão**: 2025-08-02 (6 meses)
