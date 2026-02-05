# 🔐 CREDENCIAIS DO VPS - COBRANÇA API

## 🖥️ INFORMAÇÕES DO VPS

### Hostinger VPS
- **IP Público:** 76.13.167.54
- **Servidor:** srv1298946
- **Usuário:** root
- **Porta SSH:** 22

## 🔑 MÉTODOS DE ACESSO

### Opção 1: Via Chave SSH (Recomendado)

A VPS está configurada para usar autenticação por chave SSH. Use a chave `hostinger_vps`:

```bash
ssh -i ~/.ssh/hostinger_vps root@76.13.167.54
```

Ou usando o alias configurado no SSH config:

```bash
ssh hostinger-vps
```

### Opção 2: Via Senha

Se precisar usar senha, conecte-se com:

```bash
ssh root@76.13.167.54
```

**Nota:** A senha do usuário root não está documentada neste arquivo por segurança. Você pode:
1. Verificar no painel da Hostinger
2. Usar autenticação por chave SSH (recomendado)
3. Redefinir a senha através do painel da Hostinger

## 📁 LOCALIZAÇÃO DO PROJETO

### No VPS
- **Caminho:** `/root/cobranca-api/`
- **Caminho alternativo:** `/var/www/cobranca-api/`

### Arquivo Compactado
- **Caminho:** `/home/admin/projects/cobranca-api/cobranca-api-completo.tar.gz`
- **Tamanho:** 1.1MB
- **Data:** 05/02/2026

## 📥 BAIXAR O PROJETO COMPLETO

### Via SCP
```bash
scp root@76.13.167.54:/home/admin/projects/cobranca-api/cobranca-api-completo.tar.gz .
```

### Via SFTP (FileZilla/WinSCP)
- Host: 76.13.167.54
- Usuário: root
- Porta: 22
- Arquivo: `/home/admin/projects/cobranca-api/cobranca-api-completo.tar.gz`

### Via rsync
```bash
rsync -avz root@76.13.167.54:/home/admin/projects/cobranca-api/cobranca-api-completo.tar.gz .
```

## 🔐 CREDENCIAIS DO BANCO DE DADOS

### MySQL Root
- **Usuário:** root
- **Senha:** Root@2024!Secure
- **Host:** localhost (ou container MySQL)

### MySQL Usuário da Aplicação
- **Usuário:** cobranca
- **Senha:** CobrancaAuto2026!
- **Banco:** cobranca

## 🌐 ACESSO À APLICAÇÃO

### URL Principal
- **Dashboard:** http://api.cobrancaauto.com.br/admin/saas/dashboard
- **API:** http://api.cobrancaauto.com.br/api

### Usuário Admin Principal
- **Email:** admin@cobranca.com
- **Senha:** 123456

## 🛠️ COMANDOS ÚTEIS

### Verificar Status dos Containers
```bash
ssh root@76.13.167.54 "docker ps"
```

### Ver Logs da Aplicação
```bash
ssh root@76.13.167.54 "docker logs cobranca_web --tail=50"
```

### Reiniciar Serviços
```bash
ssh root@76.13.167.54 "cd /root/cobranca-api && docker-compose restart"
```

### Ver Uso de Recursos
```bash
ssh root@76.13.167.54 "docker stats"
```

## 🔧 CONFIGURAÇÃO SSH

### Arquivo de Configuração (~/.ssh/config)
```
Host hostinger-vps
  HostName 76.13.167.54
  User root
  IdentityFile ~/.ssh/hostinger_vps
```

### Chaves SSH Disponíveis
- `~/.ssh/hostinger_vps` - Chave principal do VPS
- `~/.ssh/cobranca_deploy` - Chave para deploy
- `~/.ssh/id_ed25519_kilo` - Chave pessoal

## ⚠️ SEGURANÇA

### Recomendações Importantes

1. **Mudar Senhas Padrão**
   - Senha do root do VPS
   - Senha do MySQL root
   - Senha do usuário admin da aplicação

2. **Usar Autenticação por Chave SSH**
   - É mais segura que senha
   - Já está configurada
   - Use `ssh hostinger-vps` para acessar

3. **Manter Sistema Atualizado**
   ```bash
   ssh root@76.13.167.54 "apt-get update && apt-get upgrade -y"
   ```

4. **Verificar Logs Regularmente**
   - Logs da aplicação: `/var/www/cobranca-api/storage/logs/`
   - Logs do sistema: `/var/log/`

## 📞 SUPORTE

### Se tiver problemas de acesso:

1. **Verificar Conexão SSH**
   ```bash
   ssh -v root@76.13.167.54
   ```

2. **Verificar se o VPS está online**
   ```bash
   ping 76.13.167.54
   ```

3. **Verificar Portas**
   ```bash
   nmap -p 22,80,443 76.13.167.54
   ```

4. **Consultar Logs**
   - SSH: `/var/log/auth.log`
   - Nginx: `/var/log/nginx/error.log`
   - Aplicação: `/var/www/cobranca-api/storage/logs/laravel.log`

## 📚 DOCUMENTAÇÃO ADICIONAL

- [`INSTRUCOES_DOWNLOAD_PROJETO.md`](INSTRUCOES_DOWNLOAD_PROJETO.md) - Instruções detalhadas de download
- [`CREDENCIAIS_ACESSO.md`](CREDENCIAIS_ACESSO.md) - Credenciais da aplicação
- [`README.md`](README.md) - Documentação principal do projeto

---

**💚 Acesso VPS + Projeto Completo = Pronto para usar! 💸**
