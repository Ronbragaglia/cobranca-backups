# Checklist de Rotação de Segredos - CobrançaAuto SaaS

⚠️ **IMPORTANTE**: Execute cada etapa em ordem. NÃO pule nenhuma etapa.

---

## FASE 1: Rotação de Segredos

### ☁️ Cloudflare API Token

- [ ] Acessar: https://dash.cloudflare.com/profile/api-tokens
- [ ] Revogar token antigo (clicar em "Revoke")
- [ ] Criar novo token com permissões mínimas:
  - Zone → DNS → Edit
  - Zone → Zone → Read
  - Account → Account Settings → Read
- [ ] Definir TTL: 30 dias (recomendado)
- [ ] Copiar NOVO token (não será exibido novamente)
- [ ] Confirmar que o token antigo foi revogado

### 🖥️ Senha Root (Hostinger)

- [ ] Acessar: https://hpanel.hostinger.com/
- [ ] Ir para: Servers → VPS → Manage
- [ ] Procurar por: "Password" ou "Root Password"
- [ ] Clicar em "Change Password"
- [ ] Digitar NOVA senha (mínimo 12 caracteres, com maiúsculas, minúsculas, números e caracteres especiais)
- [ ] Exemplo forte: `Root@2025!Secure#New`
- [ ] Clicar em "Change Password"
- [ ] Anotar nova senha em gerenciador de senhas
- [ ] Testar nova senha via SSH

### 📧 Token Resend

- [ ] Acessar: https://resend.com/api-keys
- [ ] Localizar chave atual
- [ ] Clicar no ícone de "Delete" ou "Revoke"
- [ ] Confirmar exclusão
- [ ] Clicar em "Create API Key"
- [ ] Dar nome descritivo: `CobrancaAuto Production - 2025`
- [ ] Configurar permissões (Email: Send)
- [ ] Clicar em "Create"
- [ ] Copiar NOVA chave (não será exibida novamente)

### 🗄️ Senha MySQL

- [ ] Acessar container MySQL na VPS
- [ ] Conectar ao MySQL como root
- [ ] Alterar senha do root: `ALTER USER 'root'@'%' IDENTIFIED BY 'NovaSenha@2025!';`
- [ ] Alterar senha do usuário: `ALTER USER 'cobranca_user'@'%' IDENTIFIED BY 'NovaSenhaDB@2025!';`
- [ ] Executar: `FLUSH PRIVILEGES;`
- [ ] Confirmar que as senhas foram alteradas

---

## FASE 2: Preparação do Servidor

### 📁 Local do .env de Produção

- [ ] Criar diretório: `/opt/cobranca-auto/` (se não existir)
- [ ] Criar arquivo: `/opt/cobranca-auto/.env`
- [ ] Definir permissões seguras:
  ```bash
  chmod 600 /opt/cobranca-auto/.env
  chown root:root /opt/cobranca-auto/.env
  ```

### 🔒️ Variáveis para Preencher

Preencha estas variáveis no arquivo `/opt/cobranca-auto/.env`:

```
# Cloudflare
CLOUDFLARE_API_TOKEN=COPIE_O_NOVO_TOKEN_AQUI

# Banco de Dados
DB_PASSWORD=COPIE_A_NOVA_SENHA_DB_AQUI
MYSQL_ROOT_PASSWORD=COPIE_A_NOVA_SENHA_ROOT_AQUI

# Email (Resend)
MAIL_PASSWORD=COPIE_A_NOVA_CHAVE_RESEND_AQUI
```

### ✅ Validação do Servidor

- [ ] Verificar que o arquivo `.env` existe em `/opt/cobranca-auto/`
- [ ] Verificar permissões: `ls -la /opt/cobranca-auto/.env` (deve ser -rw-------)
- [ ] Verificar que nenhum script imprime variáveis sensíveis
- [ ] Garantir que o diretório está protegido

---

## FASE 3: Atualização no Docker/Compose

### 📋 Método 1: env_file (Recomendado)

Editar o arquivo `docker-compose.yml` na VPS:

```yaml
services:
  app:
    env_file:
      - /opt/cobranca-auto/.env
```

### 📋 Método 2: Variáveis de Ambiente (Alternativo)

Editar o arquivo `docker-compose.yml` na VPS:

```yaml
services:
  app:
    environment:
      - CLOUDFLARE_API_TOKEN=${CLOUDFLARE_API_TOKEN}
      - DB_PASSWORD=${DB_PASSWORD}
      - MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}
      - MAIL_PASSWORD=${MAIL_PASSWORD}
```

### 🔄 Reiniciar Containers

- [ ] Parar containers: `docker-compose down`
- [ ] Iniciar containers: `docker-compose up -d`
- [ ] Verificar logs: `docker-compose logs -f`

---

## FASE 4: Validação Final

### ✅ Testes de Integração

- [ ] Testar DNS Cloudflare:
  ```bash
  curl -X GET "https://api.cloudflare.com/client/v4/user/tokens/verify" \
    -H "Authorization: Bearer SEU_NOVO_TOKEN"
  ```
- [ ] Testar conexão MySQL:
  ```bash
  docker exec cobranca_mysql mysql -u cobranca_user -p'SuaNovaSenha' -e "SELECT 1;"
  ```
- [ ] Testar envio de email:
  ```bash
  docker exec cobranca_app php artisan mail:test seu-email@exemplo.com
  ```
- [ ] Testar aplicação:
  ```bash
  curl -I https://cobrancaauto.com.br
  ```

### 📊 Checklist de Validação

- [ ] Token Cloudflare configurado e funcionando
- [ ] Senha root alterada e testada via SSH
- [ ] Token Resend configurado e emails funcionando
- [ ] Senha MySQL alterada e conexão funcionando
- [ ] Todos os containers rodando sem erros
- [ ] Aplicação acessível e funcional
- [ ] Logs sem erros críticos

---

## CRITÉRIO DE CONCLUSÃO

✅ **Todas as 4 rotações concluídas** (Cloudflare, Hostinger, Resend, MySQL)
✅ **Servidor preparado para receber segredos** (.env criado com permissões seguras)
✅ **Docker/Compose atualizado** para usar segredos do .env
✅ **Todos os testes de validação passaram**

---

## PRÓXIMO PASSO: Hardening SSH

Após concluir todas as rotações, execute:

1. Desabilitar autenticação por senha: `PasswordAuthentication no`
2. Usar apenas chaves SSH
3. Configurar porta SSH personalizada

---

**Instruções detalhadas**: [`INSTRUCOES_ROTACAO_SEGREDO.md`](INSTRUCOES_ROTACAO_SEGREDO.md)
**Relatório completo**: [`RELATORIO_REMOCAO_SEGREDOS.md`](RELATORIO_REMOCAO_SEGREDOS.md)

**Gerado em**: 2025-02-02
**Próxima revisão**: 2025-08-02 (6 meses)
