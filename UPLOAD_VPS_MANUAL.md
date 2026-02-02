# 📤 Upload do Projeto para VPS - Instruções Manuais

## 🎯 Objetivo
Fazer upload do projeto `cobranca-api` para a VPS de produção (IP: 76.13.167.54)

## 📍 Localização Atual do Projeto
```
/home/admin/projects/cobranca-api
```

## 🚀 Comando para Executar Manualmente

### Passo 1: Verificar localização atual
```bash
pwd
```
Você deve ver: `/home/admin/projects/cobranca-api`

### Passo 2: Voltar uma pasta
```bash
cd ..
```
Agora você deve estar em: `/home/admin/projects/`

### Passo 3: Fazer upload para a VPS
```bash
scp -r cobranca-api root@76.13.167.54:/root/
```

## ⚠️ O que vai acontecer durante o upload:

1. **Primeira conexão com a VPS:**
   ```
   The authenticity of host '76.13.167.54' can't be established.
   Are you sure you want to continue connecting (yes/no)?
   ```
   **Responda:** `yes`

2. **Senha do root:**
   Será solicitada a senha do usuário root na VPS
   Digite a senha e pressione Enter

3. **Progresso do upload:**
   Você verá os arquivos sendo transferidos:
   ```
   cobranca-api/.env.example         100%  1234    1.2KB/s   00:01
   cobranca-api/composer.json        100%  5678    5.6KB/s   00:01
   cobranca-api/app/Models/User.php   100%  2345    2.3KB/s   00:01
   ...
   ```

## ✅ Após o Upload Concluído

### Verificar se o projeto foi enviado com sucesso:
```bash
ssh root@76.13.167.54 "ls -la /root/ | grep cobranca-api"
```

Você deve ver:
```
drwxr-xr-x 18 root root 12288 Feb  2 13:55 cobranca-api
```

### Verificar o conteúdo do projeto na VPS:
```bash
ssh root@76.13.167.54 "ls -la /root/cobranca-api/"
```

## 📝 Notas Importantes

- **Tempo estimado:** O upload pode levar alguns minutos dependendo da velocidade da internet
- **Tamanho do projeto:** Verifique o tamanho antes de fazer o upload:
  ```bash
  du -sh /home/admin/projects/cobranca-api
  ```
- **Conexão interrompida:** Se a conexão cair, você pode usar `rsync` para continuar de onde parou:
  ```bash
  rsync -avz --progress /home/admin/projects/cobranca-api/ root@76.13.167.54:/root/cobranca-api/
  ```

## 🔧 Comando Alternativo com Caminho Completo

Se você preferir usar o caminho completo (sem mudar de diretório):
```bash
scp -r /home/admin/projects/cobranca-api root@76.13.167.54:/root/
```

## 📊 Próximos Passos (Após o Upload)

1. Acessar a VPS:
   ```bash
   ssh root@76.13.167.54
   ```

2. Navegar até o projeto:
   ```bash
   cd /root/cobranca-api
   ```

3. Verificar os arquivos:
   ```bash
   ls -la
   ```

4. Continuar com a configuração de produção (veja os scripts em `scripts/`)

---

**Data:** 2026-02-02
**Projeto:** Cobranca API
**VPS:** 76.13.167.54
**Destino:** /root/cobranca-api
