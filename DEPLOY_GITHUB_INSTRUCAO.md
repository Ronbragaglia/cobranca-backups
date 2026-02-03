# 🚀 Deploy do Cobranca API via GitHub

## 📋 Situação Atual

O projeto foi inicializado no Git e está em processo de push para o GitHub.

### Comandos Executados:
```bash
cd /home/admin/projects/cobranca-api
git init
git add .
git commit -m "Deploy inicial CobrancaAuto VPS"
git branch -M main
git remote add origin git@github.com:Ronbragaglia/cobranca-api.git
git push -u origin main
```

### Status:
- ✅ Repositório Git inicializado
- ✅ 263 arquivos adicionados (38,116 inserções)
- ✅ Commit inicial realizado
- ✅ Branch renomeada para "main"
- ✅ Remote do GitHub configurado
- ⏳ Push para GitHub em andamento (pode estar travado)

## 🔍 Problema Encontrado

O comando `git push` está demorando mais de 20 minutos, o que é anormal para um push inicial. Isso pode indicar:

1. **Problema de conexão com o GitHub**
2. **Arquivos muito grandes no repositório**
3. **Problema de autenticação SSH**

## 🚀 Próximos Passos

### Opção 1: Verificar se o Push Foi Concluído

```bash
cd /home/admin/projects/cobranca-api
git status
```

Se mostrar "nothing to commit, working tree clean", o push foi concluído com sucesso.

### Opção 2: Verificar no GitHub

Acesse: https://github.com/Ronbragaglia/cobranca-api

Se o repositório existir e tiver os arquivos, o push foi bem-sucedido.

### Opção 3: Se o Push Falhou/Travou

Se o push estiver travado, você pode:

1. **Cancelar o push atual:**
   ```bash
   # Encontrar o PID do processo git
   ps aux | grep git | grep push
   # Matar o processo (substitua <PID> pelo número do processo)
   kill <PID>
   ```

2. **Verificar arquivos grandes:**
   ```bash
   # Encontrar arquivos maiores que 50MB
   find . -type f -size +50M
   ```

3. **Adicionar arquivos grandes ao .gitignore:**
   ```bash
   echo "*.tar.gz" >> .gitignore
   echo "*.zip" >> .gitignore
   echo "node_modules/" >> .gitignore
   echo "vendor/" >> .gitignore
   git add .gitignore
   git commit -m "Adicionar arquivos grandes ao .gitignore"
   ```

4. **Tentar o push novamente:**
   ```bash
   git push -u origin main
   ```

### Opção 4: Usar HTTPS em vez de SSH

Se houver problemas com SSH, tente usar HTTPS:

```bash
# Remover remote atual
git remote remove origin

# Adicionar remote com HTTPS
git remote add origin https://github.com/Ronbragaglia/cobranca-api.git

# Fazer push (será solicitado usuário e senha/token do GitHub)
git push -u origin main
```

## 📦 Após o Push Bem-Sucedido

### Na VPS, clonar o repositório:

```bash
# Acessar a VPS
ssh root@76.13.167.54

# Navegar até /root
cd /root

# Clonar o repositório
git clone git@github.com:Ronbragaglia/cobranca-api.git
# OU
git clone https://github.com/Ronbragaglia/cobranca-api.git

# Navegar até o projeto
cd cobranca-api
```

### Instalar dependências:

```bash
# Dependências do PHP
composer install --no-dev --optimize-autoloader

# Dependências do Node.js
npm install
npm run build
```

### Configurar o ambiente:

```bash
# Copiar arquivo de exemplo
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Editar o arquivo .env com as configurações de produção
nano .env
```

### Executar migrações:

```bash
php artisan migrate --force
```

### Configurar permissões:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 📝 Notas Importantes

- **Arquivos grandes:** Se houver arquivos muito grandes (node_modules, vendor, etc.), considere adicioná-los ao .gitignore
- **Tokens do GitHub:** Se usar HTTPS, você precisará de um Personal Access Token do GitHub em vez da senha
- **Chaves SSH:** Certifique-se de que a chave SSH pública está configurada no GitHub

## 🔗 Links Úteis

- **Repositório GitHub:** https://github.com/Ronbragaglia/cobranca-api
- **Documentação Git:** https://git-scm.com/doc
- **GitHub Personal Access Tokens:** https://github.com/settings/tokens

---

**Data:** 2026-02-02
**Projeto:** Cobranca API
**VPS:** 76.13.167.54
**GitHub:** git@github.com:Ronbragaglia/cobranca-api.git
**Status:** ⏳ Push em andamento
