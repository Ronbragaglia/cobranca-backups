# 🔐 CREDENCIAIS DE ACESSO - COBRANÇA API

## 📋 ACESSO AO DASHBOARD

### Admin Principal

- **URL:** http://api.cobrancaauto.com.br/admin/saas/dashboard
- **Email:** `admin@cobranca.com`
- **Senha:** `123456`
- **Nome:** Admin
- **Tenant:** Principal (subdomain: principal)

### Admin Demo

- **URL:** http://api.cobrancaauto.com.br/admin/saas/dashboard
- **Email:** `demo@seucrm.com`
- **Senha:** `password`
- **Nome:** Admin Demo
- **Tenant:** Demo (subdomain: demo)

---

## 📋 OUTROS USUÁRIOS DISPONÍVEIS

### Admin SeuCRM (TenantSeeder)

- **Email:** `admin@seucrm.com`
- **Senha:** `password`
- **Nome:** Admin Principal
- **Tenant:** SeuCRM (subdomain: seucrm)

---

## 📋 USUÁRIOS DE TESTE (Multi-Tenant)

O sistema cria automaticamente 3 tenants com usuários admin:

### Tenant 1
- **Email:** `admin1@cliente1.com`
- **Senha:** `password123`
- **Nome:** Admin Cliente 1
- **Tenant:** Cliente 1 (subdomain: cliente1)

### Tenant 2
- **Email:** `admin2@cliente2.com`
- **Senha:** `password123`
- **Nome:** Admin Cliente 2
- **Tenant:** Cliente 2 (subdomain: cliente2)

### Tenant 3
- **Email:** `admin3@cliente3.com`
- **Senha:** `password123`
- **Nome:** Admin Cliente 3
- **Tenant:** Cliente 3 (subdomain: cliente3)

---

## 📋 USUÁRIOS BETA TESTER

O sistema tem 10 usuários beta tester (contabilidade):

1. **Roberto Silva** - roberto.silva@contabilidade.com.br
2. **Maria Santos** - maria.santos@contabilidade.com.br
3. **João Oliveira** - joao.oliveira@contabilidade.com.br
4. **Ana Costa** - ana.costa@contabilidade.com.br
5. **Carlos Ferreira** - carlos.ferreira@contabilidade.com.br
6. **Fernanda Lima** - fernanda.lima@contabilidade.com.br
7. **Pedro Almeida** - pedro.almeida@contabilidade.com.br
8. **Juliana Rodrigues** - juliana.rodrigues@contabilidade.com.br
9. **Ricardo Pereira** - ricardo.pereira@contabilidade.com.br
10. **Camila Martins** - camila.martins@contabilidade.com.br

**Nota:** Senhas dos beta testers não estão definidas nos seeders, precisam ser criadas manualmente.

---

## 🔐 SEGURANÇA IMPORTANTE

⚠️ **RECOMENDAÇÃO MUITO IMPORTANTE:**

Após fazer login pela primeira vez, **MUDE A SENHA IMEDIATAMENTE!**

### Como mudar a senha:

1. Faça login com as credenciais acima
2. Vá em: **Perfil** ou **Configurações**
3. Procure por: **Mudar Senha** ou **Alterar Senha**
4. Digite a nova senha
5. Confirme a nova senha
6. Salve as alterações

### Por que mudar a senha?

- As senhas padrão são conhecidas e podem ser usadas por pessoas não autorizadas
- Senhas fortes protegem seus dados e dos seus clientes
- É uma prática de segurança fundamental

---

## 🌐 ACESSO À API

### API Status

- **URL:** http://api.cobrancaauto.com.br/api/status
- **Método:** GET
- **Resposta:** `{"ok":true}`

### API Documentation

- **URL:** http://api.cobrancaauto.com.br/api/documentation
- **Nota:** Se você tiver documentação da API configurada

---

## 📱 ACESSO MOBILE

Se você tiver aplicativo mobile:

- **URL:** http://api.cobrancaauto.com.br
- **Use as mesmas credenciais do dashboard**

---

## 🔧 SOLUÇÃO DE PROBLEMAS DE ACESSO

### Não consegue fazer login?

1. **Verifique se está usando as credenciais corretas:**
   - Email: admin@cobranca.com
   - Senha: 123456

2. **Limpe o cache do navegador:**
   - Chrome: Ctrl + Shift + Delete
   - Firefox: Ctrl + Shift + Delete
   - Edge: Ctrl + Shift + Delete

3. **Tente em modo anônimo:**
   - Chrome: Ctrl + Shift + N
   - Firefox: Ctrl + Shift + P

4. **Verifique se o site está acessível:**
   - http://api.cobrancaauto.com.br

### Senha não funciona?

1. **Verifique se não há espaços extras**
2. **Tente copiar e colar a senha**
3. **Verifique se o Caps Lock não está ativado**
4. **Se ainda não funcionar, redefina a senha via banco de dados**

---

## 💚 PRONTO PARA USAR!

Agora você pode:
- ✅ Acessar o dashboard
- ✅ Gerenciar cobranças
- ✅ Criar clientes
- ✅ Enviar lembretes
- ✅ Monitorar pagamentos

**Acesse agora:** http://api.cobrancaauto.com.br/admin/saas/dashboard

**Email:** admin@cobranca.com
**Senha:** 123456

---

## 📞 SUPORTE

Se tiver problemas de acesso:

1. Verifique os logs do Laravel: `/var/www/cobranca-api/storage/logs/laravel.log`
2. Verifique os logs do NGINX: `/var/log/nginx/error.log`
3. Verifique os logs do PHP-FPM: `/var/log/php8.2-fpm.log`

---

**💚 Site funcionando + Credenciais = Pronto para faturar! 💸**
