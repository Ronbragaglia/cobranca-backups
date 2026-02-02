# 📊 Relatório de Upload para VPS - Problemas Encontrados

## 🎯 Objetivo
Fazer upload do projeto `cobranca-api` (90M) para a VPS de produção (IP: 76.13.167.54)

## 📋 Resumo da Situação

### Tentativas Realizadas:

1. **SCP Direto (scripts/upload-vps.sh)**
   - ✅ Iniciado com sucesso
   - ❌ Travou após ~50 minutos
   - ❌ Processo finalizado manualmente
   - 📊 Tamanho: 90M

2. **Rsync (scripts/upload-vps-rsync.sh)**
   - ❌ Comando `rsync` não disponível no sistema
   - 📊 Não foi possível executar

3. **TAR + SCP (scripts/upload-vps-tar.sh)**
   - ✅ Arquivo TAR criado com sucesso (312K)
   - ❌ SCP travou durante o upload do TAR
   - 📊 Tamanho do TAR: 312K (excluindo node_modules, vendor, .git, etc.)
   - ⏱️ Tempo decorrido: ~3 minutos (ainda travado)

## 🔍 Análise do Problema

### Sintomas:
- Processos SCP entram em estado "S" (sleeping)
- Uso de CPU permanece em 0.0%
- Conexão parece estabelecer mas a transferência não progride
- Ocorre tanto com arquivos grandes (90M) quanto pequenos (312K)

### Possíveis Causas:

1. **Problema de MTU (Maximum Transmission Unit)**
   - Pacotes muito grandes podem estar sendo fragmentados e perdidos
   - Solução: Reduzir MTU na interface de rede

2. **Firewall na VPS**
   - Pode estar bloqueando ou limitando transferências SCP
   - Solução: Verificar regras do firewall na VPS

3. **Problema de autenticação SSH**
   - Chave SSH pode não estar configurada corretamente
   - Solução: Verificar configuração SSH na VPS

4. **Problema de rede**
   - Latência alta ou perda de pacotes
   - Solução: Verificar conectividade com ping/traceroute

5. **Problema com SFTP na VPS**
   - Servidor SFTP pode estar com problemas
   - Solução: Verificar logs do SSH na VPS

## 🚀 Soluções Sugeridas

### Opção 1: Verificar Conectividade

```bash
# Testar ping
ping -c 10 76.13.167.54

# Testar traceroute
traceroute 76.13.167.54

# Testar conexão SSH simples
ssh root@76.13.167.54 "echo 'SSH OK'"

# Verificar MTU
ping -c 1 -M do -s 1472 76.13.167.54
# Se falhar, tente com valores menores: 1472, 1400, 1300, etc.
```

### Opção 2: Ajustar MTU (Se necessário)

```bash
# Verificar MTU atual
ip link show

# Reduzir MTU temporariamente (exemplo: 1400)
sudo ip link set dev eth0 mtu 1400

# Tentar o upload novamente
scp -r /home/admin/projects/cobranca-api root@76.13.167.54:/root/
```

### Opção 3: Usar SSH com Opções Específicas

```bash
# Tentar com opções de compressão e MTU ajustado
scp -C -o "IPQoS=throughput" -r /home/admin/projects/cobranca-api root@76.13.167.54:/root/

# Ou tentar com opções de keepalive
scp -o ServerAliveInterval=15 -o ServerAliveCountMax=3 -r /home/admin/projects/cobranca-api root@76.13.167.54:/root/
```

### Opção 4: Upload via Git (Se o projeto estiver em um repositório)

```bash
# Na VPS:
cd /root
git clone <URL_DO_REPOSITORIO> cobranca-api
cd cobranca-api
git checkout <BRANCH_DESEJADA>
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### Opção 5: Usar FTP/SFTP Cliente Gráfico

- Usar FileZilla, WinSCP ou outro cliente SFTP
- Conectar à VPS (IP: 76.13.167.54, Usuário: root)
- Fazer upload dos arquivos
- Clientes gráficos muitas vezes lidam melhor com problemas de conexão

### Opção 6: Verificar e Corrigir Problema na VPS

```bash
# Acessar a VPS
ssh root@76.13.167.54

# Verificar logs do SSH
tail -f /var/log/auth.log

# Verificar se o diretório /root tem espaço suficiente
df -h

# Verificar se o SFTP está funcionando
sftp -oBatchMode=no -b - root@localhost <<< "ls /root"

# Reiniciar serviço SSH (se necessário)
systemctl restart sshd
```

## 📝 Arquivos Criados Durante o Processo

1. **[`scripts/upload-vps.sh`](scripts/upload-vps.sh)** - Script automatizado com SCP
2. **[`scripts/upload-vps-rsync.sh`](scripts/upload-vps-rsync.sh)** - Script com Rsync (não disponível)
3. **[`scripts/upload-vps-tar.sh`](scripts/upload-vps-tar.sh)** - Script com TAR + SCP
4. **[`UPLOAD_VPS_MANUAL.md`](UPLOAD_VPS_MANUAL.md)** - Instruções manuais completas
5. **[`EXECUTE_UPLOAD_VPS_AGORA.txt`](EXECUTE_UPLOAD_VPS_AGORA.txt)** - Instruções rápidas
6. **[`INSTRUCOES_UPLOAD_MANUAL_ATUALIZADAS.md`](INSTRUCOES_UPLOAD_MANUAL_ATUALIZADAS.md)** - Instruções atualizadas

## 🎯 Próximos Passos Recomendados

1. **Diagnóstico:**
   - Executar os comandos de verificação de conectividade (Opção 1)
   - Verificar logs do SSH na VPS (Opção 6)

2. **Solução Rápida:**
   - Tentar ajustar o MTU (Opção 2)
   - Usar opções específicas do SCP (Opção 3)

3. **Solução Alternativa:**
   - Usar cliente SFTP gráfico (Opção 5)
   - Fazer upload via Git (Opção 4, se aplicável)

4. **Solução Profunda:**
   - Investigar e corrigir o problema na VPS (Opção 6)
   - Verificar configurações do firewall

## ⚠️ Notas Importantes

- O problema parece ser na conexão entre a máquina local e a VPS
- Tanto arquivos grandes quanto pequenos estão travando
- A chave SSH foi testada e funciona para conexões simples
- O problema específico é com a transferência de arquivos via SCP/SFTP

---

**Data:** 2026-02-02
**Projeto:** Cobranca API
**VPS:** 76.13.167.54
**Destino:** /root/cobranca-api
**Status:** ⚠️ Upload travado - Investigação necessária
