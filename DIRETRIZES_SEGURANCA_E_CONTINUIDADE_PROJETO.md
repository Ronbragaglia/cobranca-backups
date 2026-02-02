# DIRETRIZES DE SEGURANÇA E CONTINUIDADE DO PROJETO

## 1. SEGURANÇA DE ACESSO VPS

### 1.1 Configuração SSH Obrigatória
```bash
# Criar usuário não-root
adduser cobranca-deploy
usermod -aG sudo cobranca-deploy

# Configurar SSH para usuário não-root
# /etc/ssh/sshd_config:
PermitRootLogin prohibit-password
PasswordAuthentication no
PubkeyAuthentication yes
AllowUsers cobranca-deploy

# Reiniciar SSH
systemctl restart sshd
```

### 1.2 Chaves SSH
- NUNCA usar autenticação por senha
- Usar apenas chaves SSH (ed25519 ou RSA 4096+)
- Proteger chave privada com passphrase forte
- Mover chave SSH para usuário não-root

### 1.3 Firewall
- Bloquear todas as portas por padrão
- Permitir apenas: SSH (22), HTTP (80), HTTPS (443)
- Usar fail2ban para proteção contra brute force

## 2. GERENCIAMENTO DE SEGREDOS

### 2.1 Regras de Ouro
- NUNCA commitar senhas, tokens ou API keys em texto
- NUNCA usar .env no versionamento
- Usar variáveis de ambiente para todos os segredos
- Rotacionar chaves periodicamente (90 dias)

### 2.2 Segredos que DEVEM ser protegidos
- Senhas de banco de dados
- API keys (Stripe, Cloudflare, Evolution API)
- Tokens de autenticação
- Chaves privadas SSH
- Webhook secrets
- Application secrets (APP_KEY Laravel)

### 2.3 .env.example
- Deve conter apenas placeholders
- NUNCA valores reais
- Exemplo: `DB_PASSWORD=your_password_here`

## 3. DOCKER E CONTAINERS

### 3.1 Hardening Docker
- NUNCA rodar containers como root
- Usar `user: www-data` ou similar
- Isolar redes Docker (bridge networks)
- Limitar recursos (CPU, memória)
- Usar read-only filesystem quando possível

### 3.2 Portas Expostas
- Expor apenas portas necessárias
- Documentar justificativa de cada porta
- Usar proxy reverso (Traefik/Nginx)
- NUNCA expor portas de admin diretamente

### 3.3 Imagens
- Usar imagens oficiais
- Especificar versões (não usar latest)
- Atualizar regularmente
- Escanear vulnerabilidades

## 4. LARAVEL PRODUÇÃO

### 4.1 Configurações Obrigatórias
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com
```

### 4.2 Segurança
- TRUSTED_PROXIES configurado com IPs Cloudflare
- Rate limiting ativo (throttle:api)
- CSRF protection habilitado
- CORS configurado corretamente
- HTTPS forçado

### 4.3 Logs
- Configurar rotação de logs
- NÃO logar dados sensíveis (senhas, tokens)
- Usar nível apropriado (production: error/warning)
- Monitorar logs em tempo real

## 5. CLOUDFLARE E PROXIES

### 5.1 Configuração Obrigatória
- Proxy Cloudflare ATIVO em todos os domínios
- Firewall WAF configurado
- SSL/TLS Full (Strict)
- Auto Minify habilitado
- Brotli habilitado

### 5.2 Proteções
- Bot Fight Mode
- Security Level High
- Challenge Passage
- Rate Limiting

### 5.3 IPs de Confiança
- NUNCA confiar em IP direto do usuário
- Usar apenas `X-Forwarded-For` do Cloudflare
- Validar IPs de webhooks

## 6. BACKUP E CONTINUIDADE

### 6.1 Backups Automáticos
- Banco de dados: backup diário
- Arquivos: backup semanal
- Retenção: 30 dias
- Testar restauração mensalmente

### 6.2 Monitoramento
- Uptime monitoring (UptimeRobot)
- Alertas de erros
- Métricas de performance
- Logs centralizados

### 6.3 Plano de Recuperação
- Documentar procedimentos de restore
- Ter backup off-site
- Testar DR (Disaster Recovery) trimestralmente
- SLA: RTO < 4 horas, RPO < 1 hora

## 7. COMPLIANCE E AUDITORIA

### 7.1 LGPD
- Dados criptografados em repouso
- Logs de acesso auditáveis
- Política de retenção de dados
- Direito ao esquecimento implementado

### 7.2 PCI-DSS (se aplicável)
- NUNCA armazenar dados de cartão
- Usar tokenização (Stripe)
- Compliance com SAQ A-EP

## 8. CHECKLIST DE DEPLOY

### Pré-Deploy
- [ ] APP_DEBUG=false
- [ ] APP_KEY gerada
- [ ] .env configurado
- [ ] Migrations executadas
- [ ] Cache limpo
- [ ] Permissões corretas
- [ ] SSL válido
- [ ] Firewall configurado
- [ ] Monitoramento ativo
- [ ] Backups agendados

### Pós-Deploy
- [ ] Testar endpoints críticos
- [ ] Verificar logs de erro
- [ ] Testar webhooks
- [ ] Validar monitoramento
- [ ] Documentar mudanças

## 9. BLOQUEADORES PARA PRODUÇÃO

### CRÍTICOS (impedem deploy)
- Senhas em texto no código
- APP_DEBUG=true
- Root SSH habilitado
- Portas não necessárias expostas
- Sem backup configurado
- Sem monitoramento

### IMPORTANTES (deve corrigir em 24h)
- Rate limiting desabilitado
- Logs com dados sensíveis
- Sem rotação de logs
- Sem WAF configurado
- IPs não validados

## 10. CONTATO E ESCALADA

### Emergência de Segurança
- Responsável: [NOME]
- Email: [EMAIL]
- Telefone: [TELEFONE]

### Procedimento de Incidente
1. Identificar e conter
2. Notificar stakeholders
3. Investigar causa raiz
4. Implementar correção
5. Documentar lições aprendidas

---

**ÚLTIMA ATUALIZAÇÃO:** 2026-02-02
**VERSÃO:** 1.0
**STATUS:** EM IMPLEMENTAÇÃO
