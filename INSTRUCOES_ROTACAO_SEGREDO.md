# Instruções de Rotação de Segredos - CobrançaAuto SaaS

⚠️ **IMPORTANTE**: Este documento contém instruções para rotação de segredos. Execute todas as etapas em ordem e não pule nenhuma etapa.

## Status dos Segredos

**ANTES DA ROTAÇÃO** (segredos expostos no código):
- ❌ Cloudflare API Token: `l7EN2FvAklLm0bfXLT-unMQ3mwMO8vUm73JroUpI` (EXPÔSTO)
- ❌ DB Password: `Cobranca@2024!Secure` (EXPÔSTO)
- ❌ MySQL Root Password: `Root@2024!Secure` (EXPÔSTO)
- ❌ Resend API Key: `re_XYZ123456789` (EXPÔSTO)

**APÓS ROTAÇÃO** (segredos configurados no servidor):
- ✅ Todos os segredos substituídos por `__SET_IN_SERVER_ONLY__` no código
- ✅ Segredos reais configurados apenas no servidor via variáveis de ambiente

---

## Checklist de Rotação de Segredos

### 1. Rotação do Token Cloudflare

#### 1.1 Revogar Token Antigo
1. Acesse: https://dash.cloudflare.com/profile/api-tokens
2. Localize o token atual: `l7EN2FvAklLm0bfXLT-unMQ3mwMO8vUm73JroUpI`
3. Clique em "Revoke" ao lado do token
4. Confirme a revogação

#### 1.2 Criar Novo Token
1. Clique em "Create Token"
2. Selecione o template "Custom token"
3. Configure as permissões:
   - **Zone** → **DNS** → **Edit**
   - **Zone** → **Zone** → **Read**
   - **Account** → **Account Settings** → **Read**
4. Em "Account Resources", selecione:
   - Include → Specific account → [Sua conta]
5. Em "Zone Resources", selecione:
   - Include → Specific zone → `cobrancaauto.com.br`
6. Defina "Client IP Address Filtering" (opcional, mas recomendado):
   - Selecione o IP da sua VPS: `76.13.167.54`
7. Defina "TTL" (Time to Live):
   - Recomendado: "Custom" → 30 dias
8. Clique em "Continue to summary"
9. Revise as permissões e clique em "Create Token"
10. **COPIE O NOVO TOKEN IMEDIATAMENTE** (não será exibido novamente)

#### 1.3 Atualizar no Servidor
```bash
# SSH na VPS
ssh root@76.13.167.54

# Editar o arquivo .env
cd /opt/app
nano .env

# Substituir a linha:
# CLOUDFLARE_API_TOKEN=l7EN2FvAklLm0bfXLT-unMQ3mwMO8vUm73JroUpI
# Por:
CLOUDFLARE_API_TOKEN=SEU_NOVO_TOKEN_AQUI

# Salvar e sair (Ctrl+O, Enter, Ctrl+X)

# Reiniciar containers
docker-compose down
docker-compose up -d

# Verificar logs
docker-compose logs -f traefik
```

---

### 2. Rotação da Senha Root (Hostinger)

#### 2.1 Alterar Senha Root no hPanel
1. Acesse: https://hpanel.hostinger.com/
2. Faça login com suas credenciais atuais
3. Vá para: **Servers** → **VPS** → **Manage**
4. Procure por: **Password** ou **Root Password**
5. Clique em "Change Password"
6. Digite a nova senha (requisitos mínimos):
   - Mínimo 12 caracteres
   - Letras maiúsculas e minúsculas
   - Números
   - Caracteres especiais
   - Exemplo: `Root@2025!Secure#New`
7. Clique em "Change Password"
8. Anote a nova senha em um local seguro (gerenciador de senhas)

#### 2.2 Testar Nova Senha
```bash
# Tentar conectar com nova senha
ssh root@76.13.167.54
# Digite a nova senha quando solicitado

# Se conectar com sucesso, a rotação foi concluída
```

---

### 3. Rotação do Token Resend

#### 3.1 Revogar Token Antigo
1. Acesse: https://resend.com/api-keys
2. Localize a chave atual: `re_XYZ123456789`
3. Clique no ícone de "Delete" ou "Revoke"
4. Confirme a exclusão

#### 3.2 Criar Nova Chave
1. Clique em "Create API Key"
2. Dê um nome descritivo:
   - Exemplo: `CobrancaAuto Production - 2025`
3. Configure as permissões (se aplicável):
   - Email: Send
4. Clique em "Create"
5. **COPIE A NOVA CHAVE IMEDIATAMENTE**

#### 3.3 Atualizar no Servidor
```bash
# SSH na VPS
ssh root@76.13.167.54

# Editar o arquivo .env
cd /opt/app
nano .env

# Substituir a linha:
# MAIL_PASSWORD=re_XYZ123456789
# Por:
MAIL_PASSWORD=SEU_NOVO_TOKEN_AQUI

# Salvar e sair (Ctrl+O, Enter, Ctrl+X)

# Reiniciar containers
docker-compose down
docker-compose up -d

# Verificar logs
docker-compose logs -f app
```

#### 3.4 Testar Envio de Email
```bash
# Acessar o container app
docker exec -it cobranca_app bash

# Testar envio de email
php artisan tinker
>>> Mail::raw('Teste de email', function($message) {
...     $message->to('seu-email@exemplo.com')->subject('Teste de Rotação');
... });
=> true

# Se receber o email, a rotação foi bem-sucedida
```

---

### 4. Rotação da Senha MySQL

#### 4.1 Alterar Senha do MySQL Root
```bash
# SSH na VPS
ssh root@76.13.167.54

# Acessar o container MySQL
docker exec -it cobranca_mysql bash

# Conectar ao MySQL como root
mysql -u root -p
# Digite a senha atual: Root@2024!Secure

# Alterar senha do root
ALTER USER 'root'@'%' IDENTIFIED BY 'SuaNovaSenha@2025!';
FLUSH PRIVILEGES;
EXIT;

# Sair do container
exit
```

#### 4.2 Alterar Senha do Usuário do Banco
```bash
# Acessar o container MySQL
docker exec -it cobranca_mysql bash

# Conectar ao MySQL como root
mysql -u root -p
# Digite a NOVA senha do root

# Alterar senha do usuário cobranca_user
ALTER USER 'cobranca_user'@'%' IDENTIFIED BY 'NovaSenhaDB@2025!';
FLUSH PRIVILEGES;
EXIT;

# Sair do container
exit
```

#### 4.3 Atualizar no Servidor
```bash
# Editar o arquivo .env
cd /opt/app
nano .env

# Substituir as linhas:
# DB_PASSWORD=Cobranca@2024!Secure
# MYSQL_ROOT_PASSWORD=Root@2024!Secure
# Por:
DB_PASSWORD=NovaSenhaDB@2025!
MYSQL_ROOT_PASSWORD=SuaNovaSenha@2025!

# Salvar e sair (Ctrl+O, Enter, Ctrl+X)

# Editar o docker-compose.yml (se necessário)
nano docker-compose.yml

# Substituir as variáveis de ambiente:
# MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
# MYSQL_PASSWORD: ${MYSQL_PASSWORD}

# Salvar e sair

# Reiniciar containers
docker-compose down
docker-compose up -d

# Verificar logs
docker-compose logs -f mysql
```

#### 4.4 Testar Conexão com o Banco
```bash
# Acessar o container app
docker exec -it cobranca_app bash

# Testar conexão
php artisan tinker
>>> DB::connection()->getPdo();
=> PDO { ... }

# Se não houver erro, a conexão está funcionando
```

---

## Como Configurar Segredos no Servidor

### Método 1: Variáveis de Ambiente (.env)
```bash
# Criar arquivo .env no servidor
cd /opt/app
nano .env

# Adicionar os segredos:
CLOUDFLARE_API_TOKEN=seu_token_aqui
DB_PASSWORD=sua_senha_aqui
MYSQL_ROOT_PASSWORD=sua_senha_root_aqui
MAIL_PASSWORD=seu_token_resend_aqui

# Salvar e sair

# Definir permissões seguras
chmod 600 .env
chown root:root .env

# Reiniciar containers
docker-compose down
docker-compose up -d
```

### Método 2: Docker Secrets (Recomendado para produção)
```bash
# Criar secrets
echo "seu_token_aqui" | docker secret create cloudflare_api_token -
echo "sua_senha_aqui" | docker secret create db_password -
echo "sua_senha_root_aqui" | docker secret create mysql_root_password -
echo "seu_token_resend_aqui" | docker secret create resend_api_key -

# Editar docker-compose.yml para usar secrets
nano docker-compose.yml

# Substituir:
# environment:
#   CLOUDFLARE_API_TOKEN: ${CLOUDFLARE_API_TOKEN}
# Por:
# secrets:
#   - cloudflare_api_token

# E adicionar:
# secrets:
#   cloudflare_api_token:
#     external: true

# Reiniciar com secrets
docker-compose down
docker-compose up -d
```

### Método 3: Export no Shell (Temporário)
```bash
# Exportar variáveis antes de iniciar containers
export CLOUDFLARE_API_TOKEN=seu_token_aqui
export DB_PASSWORD=sua_senha_aqui
export MYSQL_ROOT_PASSWORD=sua_senha_root_aqui
export MAIL_PASSWORD=seu_token_resend_aqui

# Iniciar containers
docker-compose up -d
```

---

## Verificação Final

### Checklist de Validação
- [ ] Token Cloudflare revogado e novo token criado
- [ ] Novo token Cloudflare configurado no servidor
- [ ] Senha root do Hostinger alterada
- [ ] Nova senha root testada via SSH
- [ ] Token Resend revogado e novo token criado
- [ ] Novo token Resend configurado no servidor
- [ ] Email de teste enviado com sucesso
- [ ] Senha MySQL root alterada
- [ ] Senha usuário MySQL alterada
- [ ] Novas senhas configuradas no servidor
- [ ] Containers reiniciados com sucesso
- [ ] Aplicação funcionando normalmente
- [ ] Logs sem erros

### Testes de Integração
```bash
# Testar DNS Cloudflare
curl -X GET "https://api.cloudflare.com/client/v4/user/tokens/verify" \
  -H "Authorization: Bearer SEU_NOVO_TOKEN"

# Testar conexão MySQL
docker exec cobranca_mysql mysql -u cobranca_user -p'SuaNovaSenha' -e "SELECT 1;"

# Testar envio de email
php artisan mail:test seu-email@exemplo.com

# Testar aplicação
curl -I https://cobrancaauto.com.br
```

---

## Próximo Passo: Hardening SSH

Após concluir a rotação de todos os segredos, execute o hardening do SSH:

### 1. Desabilitar Autenticação por Senha
```bash
# Editar configuração SSH
nano /etc/ssh/sshd_config

# Alterar:
PasswordAuthentication yes
# Para:
PasswordAuthentication no

# Salvar e sair

# Reiniciar SSH
systemctl restart sshd
```

### 2. Usar Apenas Chaves SSH
```bash
# Gerar par de chaves (se ainda não tiver)
ssh-keygen -t ed25519 -C "seu-email@exemplo.com"

# Copiar chave pública para o servidor
ssh-copy-id root@76.13.167.54

# Testar conexão sem senha
ssh root@76.13.167.54
```

### 3. Configurar Porta SSH Personalizada
```bash
# Editar configuração SSH
nano /etc/ssh/sshd_config

# Alterar:
#Port 22
# Para:
Port 2222

# Salvar e sair

# Atualizar firewall (ufw)
ufw allow 2222/tcp
ufw delete allow 22/tcp
ufw reload

# Reiniciar SSH
systemctl restart sshd
```

### 4. Testar Nova Configuração SSH
```bash
# Testar conexão na nova porta
ssh -p 2222 root@76.13.167.54

# Se conectar, a configuração está correta
```

---

## Documentação de Referência

- [Cloudflare API Tokens](https://developers.cloudflare.com/api/tokens/)
- [Resend API Keys](https://resend.com/docs/api-reference/authentication)
- [MySQL User Management](https://dev.mysql.com/doc/refman/8.0/en/account-management-sql.html)
- [Docker Secrets](https://docs.docker.com/engine/swarm/secrets/)
- [SSH Hardening](https://www.sshaudit.com/hardening_guides.html)

---

## Suporte

Se encontrar problemas durante a rotação:
1. Verifique os logs dos containers: `docker-compose logs -f`
2. Confirme que as variáveis de ambiente estão corretas: `docker-compose config`
3. Teste cada serviço individualmente antes de reiniciar tudo
4. Mantenha um backup do arquivo .env antes de alterar

**Última atualização**: 2025-02-02
**Próxima revisão recomendada**: 2025-08-02 (6 meses)
