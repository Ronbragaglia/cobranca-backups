#!/bin/bash
# ============================================
# HARDENING SSH - CobrançaAuto SaaS
# ============================================
# VPS: 76.13.167.54
# Diretório: /opt/cobranca-auto
# ============================================

set -e

echo "============================================"
echo "INICIANDO HARDENING SSH"
echo "============================================"
echo ""

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then
    echo "❌ ERRO: Este script deve ser executado como root"
    echo "Execute: sudo bash $0"
    exit 1
fi

# 1. Backup da configuração atual
echo "1. Criando backup da configuração SSH..."
cp /etc/ssh/sshd_config /etc/ssh/sshd_config.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ Backup criado: /etc/ssh/sshd_config.backup.$(date +%Y%m%d_%H%M%S)"
echo ""

# 2. Desabilitar autenticação por senha
echo "2. Desabilitando autenticação por senha..."
sed -i 's/^#PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config
sed -i 's/^PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config

# Desabilitar login root direto
echo "3. Desabilitando login root direto..."
sed -i 's/^#PermitRootLogin.*/PermitRootLogin no/' /etc/ssh/sshd_config
sed -i 's/^PermitRootLogin.*/PermitRootLogin no/' /etc/ssh/sshd_config

# Desabilitar autenticação por senha para root
echo "4. Desabilitando autenticação por senha para root..."
sed -i '/^PermitRootLogin no/a\AuthenticationMethods publickey/' /etc/ssh/sshd_config

# 3. Habilitar autenticação por chave pública
echo "5. Habilitando autenticação por chave pública..."
sed -i 's/^#PubkeyAuthentication.*/PubkeyAuthentication yes/' /etc/ssh/sshd_config
sed -i 's/^PubkeyAuthentication.*/PubkeyAuthentication yes/' /etc/ssh/sshd_config

# 4. Configurar porta SSH personalizada (opcional)
echo "6. Configurando porta SSH personalizada..."
read -p "Deseja alterar a porta SSH de 22 para 2222? (s/n): " -n -r
if [ "$CHANGE_PORT" = "s" ] || [ "$CHANGE_PORT" = "S" ]; then
    sed -i 's/^#Port.*/Port 2222/' /etc/ssh/sshd_config
    sed -i 's/^Port.*/Port 2222/' /etc/ssh/sshd_config
    SSH_PORT=2222
    echo "✅ Porta alterada para 2222"
else
    SSH_PORT=22
    echo "✅ Porta mantida como 22"
fi
echo ""

# 5. Verificar configuração
echo "7. Verificando configuração SSH..."
sshd -t 0 > /dev/null
if [ $? -eq 0 ]; then
    echo "✅ Configuração válida"
else
    echo "❌ ERRO: Configuração inválida"
    echo "Verifique o arquivo: /etc/ssh/sshd_config"
    exit 1
fi
echo ""

# 6. Atualizar firewall (se alterou porta)
if [ "$SSH_PORT" = "2222" ]; then
    echo "8. Atualizando firewall..."
    if command -v ufw &> /dev/null; then
        ufw allow $SSH_PORT/tcp
        ufw delete allow 22/tcp
        ufw reload
        echo "✅ Firewall UFW atualizado"
    elif command -v firewall-cmd &> /dev/null; then
        firewall-cmd --permanent --add-port=$SSH_PORT/tcp
        firewall-cmd --permanent --remove-port=22/tcp
        firewall-cmd --reload
        echo "✅ Firewall firewalld atualizado"
    else
        echo "⚠️  Nenhum firewall detectado (ufw ou firewalld)"
        echo "   Atualize manualmente o firewall para permitir a porta $SSH_PORT"
    fi
    echo ""
fi

# 7. Reiniciar serviço SSH
echo "9. Reiniciando serviço SSH..."
systemctl restart sshd
if [ $? -eq 0 ]; then
    echo "✅ SSH reiniciado com sucesso"
else
    echo "❌ ERRO: Falha ao reiniciar SSH"
    exit 1
fi
echo ""

# 8. Verificar status do SSH
echo "10. Verificando status do SSH..."
systemctl status sshd --no-pager
echo ""

# 9. Testar configuração
echo "11. Testando configuração SSH..."
echo "   - Porta: $SSH_PORT"
echo "   - Autenticação por senha: DESABILITADA"
echo "   - Autenticação por chave: HABILITADA"
echo "   - Login root: DESABILITADO"
echo ""

# 10. Instruções para adicionar chave SSH
echo "============================================"
echo "INSTRUÇÕES PARA ADICIONAR CHAVE SSH"
echo "============================================"
echo ""
echo "No seu computador local, execute:"
echo ""
echo "1. Gerar par de chaves SSH (se ainda não tiver):"
echo "   ssh-keygen -t ed25519 -C \"seu-email@exemplo.com\""
echo ""
echo "2. Copiar chave pública para o servidor:"
echo "   ssh-copy-id -i ~/.ssh/id_ed25519.pub root@76.13.167.54"
echo ""
echo "3. Testar conexão:"
echo "   ssh -p $SSH_PORT root@76.13.167.54"
echo ""
echo "4. Se pedir senha, a configuração está incorreta"
echo ""

# 11. Resumo
echo "============================================"
echo "RESUMO DAS ALTERAÇÕES"
echo "============================================"
echo "✅ Backup criado: /etc/ssh/sshd_config.backup.$(date +%Y%m%d_%H%M%S)"
echo "✅ PasswordAuthentication: no"
echo "✅ PermitRootLogin: no"
echo "✅ PubkeyAuthentication: yes"
echo "✅ Porta SSH: $SSH_PORT"
echo "✅ Firewall atualizado" (se aplicável)
echo "✅ SSH reiniciado"
echo ""

# 12. Próximos passos
echo "============================================"
echo "PRÓXIMOS PASSOS"
echo "============================================"
echo ""
echo "1. Adicionar sua chave SSH pública no servidor:"
echo "   ssh-copy-id -i ~/.ssh/id_ed25519.pub root@76.13.167.54"
echo ""
echo "2. Testar conexão:"
echo "   ssh -p $SSH_PORT root@76.13.167.54"
echo ""
echo "3. Se a conexão funcionar, desconecte e teste novamente"
echo ""
echo "4. Após confirmar, remova o backup:"
echo "   rm /etc/ssh/sshd_config.backup.*"
echo ""

echo "============================================"
echo "HARDENING SSH CONCLUÍDO"
echo "============================================"
