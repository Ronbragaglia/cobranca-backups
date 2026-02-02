# 📤 Upload do Projeto para VPS - Instruções Atualizadas

## 🎯 Situação Atual

O upload via SCP foi iniciado mas parece ter travado após cerca de 50 minutos. O processo foi finalizado.

## 🚀 Opções para Fazer o Upload

### Opção 1: SCP com Caminho Completo (Mais Simples)

```bash
scp -r /home/admin/projects/cobranca-api root@76.13.167.54:/root/
```

**Notas:**
- Se for a primeira conexão, responda `yes` quando solicitado
- A chave SSH deve entrar automaticamente (já testado que funciona)
- O upload pode levar alguns minutos dependendo da velocidade da conexão

### Opção 2: SCP com Compressão (Mais Rápido)

```bash
scp -C -r /home/admin/projects/cobranca-api root@76.13.167.54:/root/
```

A opção `-C` ativa a compressão durante a transferência, o que pode acelerar o upload.

### Opção 3: Upload em Partes (Se o SCP Continuar Travando)

Se o SCP continuar travando, você pode fazer o upload em partes menores:

#### Passo 1: Criar a estrutura básica
```bash
ssh root@76.13.167.54 "mkdir -p /root/cobranca-api"
```

#### Passo 2: Upload dos arquivos principais (sem node_modules e vendor)
```bash
cd /home/admin/projects/cobranca-api
tar --exclude='node_modules' --exclude='vendor' --exclude='.git' -czf project.tar.gz .
scp project.tar.gz root@76.13.167.54:/root/cobranca-api/
```

#### Passo 3: Extrair na VPS
```bash
ssh root@76.13.167.54 "cd /root/cobranca-api && tar -xzf project.tar.gz && rm project.tar.gz"
```

### Opção 4: Usar Rsync (Se Disponível)

Primeiro verifique se o rsync está instalado:
```bash
which rsync
```

Se estiver instalado, use:
```bash
rsync -avz --progress \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.git' \
    --exclude='.env' \
    /home/admin/projects/cobranca-api/ root@76.13.167.54:/root/cobranca-api/
```

## ✅ Verificação Após o Upload

### Verificar se o projeto foi enviado:
```bash
ssh root@76.13.167.54 "ls -la /root/ | grep cobranca-api"
```

Você deve ver algo como:
```
drwxr-xr-x 18 root root 12288 Feb  2 14:55 cobranca-api
```

### Verificar o conteúdo do projeto:
```bash
ssh root@76.13.167.54 "ls -la /root/cobranca-api/"
```

### Verificar o tamanho do projeto na VPS:
```bash
ssh root@76.13.167.54 "du -sh /root/cobranca-api"
```

Deve mostrar algo próximo de `90M`

## 📋 Próximos Passos (Após o Upload Bem-Sucedido)

### 1. Acessar a VPS:
```bash
ssh root@76.13.167.54
```

### 2. Navegar até o projeto:
```bash
cd /root/cobranca-api
```

### 3. Verificar os arquivos:
```bash
ls -la
```

### 4. Instalar dependências do PHP:
```bash
composer install --no-dev --optimize-autoloader
```

### 5. Instalar dependências do Node.js:
```bash
npm install
npm run build
```

### 6. Configurar o ambiente:
```bash
cp .env.example .env
php artisan key:generate
```

### 7. Editar o arquivo .env com as configurações de produção:
```bash
nano .env
```

### 8. Executar migrações:
```bash
php artisan migrate --force
```

### 9. Configurar permissões:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## ⚠️ Solução de Problemas

### Se o SCP continuar travando:

1. **Verifique a conexão:**
   ```bash
   ping -c 5 76.13.167.54
   ```

2. **Teste a conexão SSH:**
   ```bash
   ssh root@76.13.167.54 "echo 'Conexão OK'"
   ```

3. **Tente fazer upload de um arquivo pequeno primeiro:**
   ```bash
   echo "teste" > /tmp/teste.txt
   scp /tmp/teste.txt root@76.13.167.54:/tmp/
   ssh root@76.13.167.54 "cat /tmp/teste.txt"
   ```

4. **Se funcionar, tente o upload completo novamente:**
   ```bash
   scp -r /home/admin/projects/cobranca-api root@76.13.167.54:/root/
   ```

### Se o upload for muito lento:

- Use a opção de compressão: `scp -C -r ...`
- Faça o upload em horários com menos tráfego de rede
- Considere usar a Opção 3 (upload em partes usando tar)

## 📊 Informações do Projeto

- **Tamanho local:** 90M
- **VPS IP:** 76.13.167.54
- **Usuário:** root
- **Destino:** /root/cobranca-api
- **Projeto local:** /home/admin/projects/cobranca-api

## 📄 Scripts Disponíveis

- [`scripts/upload-vps.sh`](scripts/upload-vps.sh) - Script automatizado com SCP
- [`scripts/upload-vps-rsync.sh`](scripts/upload-vps-rsync.sh) - Script com Rsync (se disponível)
- [`UPLOAD_VPS_MANUAL.md`](UPLOAD_VPS_MANUAL.md) - Instruções manuais completas
- [`EXECUTE_UPLOAD_VPS_AGORA.txt`](EXECUTE_UPLOAD_VPS_AGORA.txt) - Instruções rápidas

---

**Data:** 2026-02-02
**Projeto:** Cobranca API
**VPS:** 76.13.167.54
**Destino:** /root/cobranca-api
