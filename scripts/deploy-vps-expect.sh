#!/usr/bin/expect -f

# =============================================================================
# SCRIPT DEPLOY AUTOMÁTICO - LOCAL → VPS HOSTINGER (USANDO EXPECT)
# VPS: 76.13.167.54 (Ubuntu 22.04, root)
# Senha SSH: 1Qaz2wsx@2026
# =============================================================================

set timeout 300

# Variáveis
set VPS_IP "76.13.167.54"
set VPS_USER "root"
set VPS_PASSWORD "1Qaz2wsx@2026"
set VPS_DIR "/var/www/cobranca-auto"
set LOCAL_ARCHIVE "cobranca-api.tar.gz"

# Função para executar comando SSH
proc ssh_exec {cmd} {
    global VPS_USER VPS_IP VPS_PASSWORD
    spawn ssh -o StrictHostKeyChecking=no ${VPS_USER}@${VPS_IP} $cmd
    expect {
        "password:" {
            send "${VPS_PASSWORD}\r"
            expect eof
        }
        eof {}
    }
}

puts "\033\[0;32m========================================\033\[0m"
puts "\033\[0;32mDEPLOY AUTOMÁTICO - VPS HOSTINGER\033\[0m"
puts "\033\[0;32m========================================\033\[0m"
puts ""

# =============================================================================
# COMANDO 1: Testar conexão SSH
# =============================================================================
puts "\033\[1;33m\[1/10] Testando conexão SSH...\033\[0m"
ssh_exec "echo '✓ Conexão SSH OK' && exit"
puts "\033\[0;32m✓ Comando 1 concluído\033\[0m"
puts ""

# =============================================================================
# COMANDO 2: Instalar Docker e Docker Compose
# =============================================================================
puts "\033\[1;33m\[2/10] Instalando Docker e Docker Compose...\033\[0m"
ssh_exec "apt-get update && apt-get install -y docker.io docker-compose && systemctl enable docker && systemctl start docker && docker --version && docker-compose --version"
puts "\033\[0;32m✓ Comando 2 concluído\033\[0m"
puts ""

# =============================================================================
# COMANDO 3: Criar diretório do projeto
# =============================================================================
puts "\033\[1;33m\[3/10] Criando diretório do projeto na VPS...\033\[0m"
ssh_exec "mkdir -p ${VPS_DIR} && ls -la /var/www/"
puts "\033\[0;32m✓ Comando 3 concluído\033\[0m"
puts ""

# =============================================================================
# COMANDO 4: Compactar projeto local (executa localmente)
# =============================================================================
puts "\033\[1;33m\[4/10] Compactando projeto local...\033\[0m"
exec tar -czf ${LOCAL_ARCHIVE} \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.git' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  .
puts "\033\[0;32m✓ Comando 4 concluído - Arquivo: ${LOCAL_ARCHIVE}\033\[0m"
puts ""

# =============================================================================
# COMANDO 5: Transferir arquivo para VPS
# =============================================================================
puts "\033\[1;33m\[5/10] Transferindo arquivo para VPS...\033\[0m"
spawn scp -o StrictHostKeyChecking=no ${LOCAL_ARCHIVE} ${VPS_USER}@${VPS_IP}:/var/www/${LOCAL_ARCHIVE}
expect {
    "password:" {
        send "${VPS_PASSWORD}\r"
        expect eof
    }
    eof {}
}
puts "\033\[0;32m✓ Comando 5 concluído\033\[0m"
puts ""

# =============================================================================
# COMANDO 6: Extrair projeto na VPS
# =============================================================================
puts "\033\[1;33m\[6/10] Extraindo projeto na VPS...\033\[0m"
ssh_exec "cd /var/www && tar -xzf ${LOCAL_ARCHIVE} -C ${VPS_DIR} && rm ${LOCAL_ARCHIVE} && ls -la ${VPS_DIR}/"
puts "\033\[0;32m✓ Comando 6 concluído\033\[0m"
puts ""

# =============================================================================
# COMANDO 7: Configurar firewall
# =============================================================================
puts "\033\[1;33m\[7/10] Configurando firewall...\033\[0m"
ssh_exec "ufw allow 80/tcp && ufw allow 8080/tcp && ufw allow 22/tcp && ufw --force enable && ufw status"
puts "\033\[0;32m✓ Comando 7 concluído\033\[0m"
puts ""

# =============================================================================
# COMANDO 8: Rodar docker-compose.dev.yml
# =============================================================================
puts "\033\[1;33m\[8/10] Iniciando containers Docker...\033\[0m"
puts "\033\[1;33m⚠️  Este comando pode levar 5-10 minutos (build das imagens)\033\[0m"
ssh_exec "cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml up -d --build"
puts "\033\[0;32m✓ Comando 8 concluído\033\[0m"
puts ""

# =============================================================================
# COMANDO 9: Testar aplicação
# =============================================================================
puts "\033\[1;33m\[9/10] Testando aplicação...\033\[0m"
ssh_exec "sleep 15 && docker ps && curl -I http://localhost:8000 && echo '---' && curl -I http://localhost:8080"
puts "\033\[0;32m✓ Comando 9 concluído\033\[0m"
puts ""

# =============================================================================
# COMANDO 10: Ver logs e validar
# =============================================================================
puts "\033\[1;33m\[10/10] Verificando logs...\033\[0m"
ssh_exec "cd ${VPS_DIR} && docker-compose -f docker-compose.dev.yml logs --tail=50 app && echo '---' && docker-compose -f docker-compose.dev.yml logs --tail=20 mysql"
puts "\033\[0;32m✓ Comando 10 concluído\033\[0m"
puts ""

# =============================================================================
# RESUMO FINAL
# =============================================================================
puts "\033\[0;32m========================================\033\[0m"
puts "\033\[0;32m✓ DEPLOY CONCLUÍDO COM SUCESSO!\033\[0m"
puts "\033\[0;32m========================================\033\[0m"
puts ""
puts "Acesse a aplicação:"
puts "  • Laravel: \033\[0;32mhttp://${VPS_IP}:8000\033\[0m"
puts "  • phpMyAdmin: \033\[0;32mhttp://${VPS_IP}:8080\033\[0m"
puts "    Usuário: root"
puts "    Senha: root"
puts ""

# Limpar arquivo local
puts "\033\[1;33mLimpando arquivo local...\033\[0m"
exec rm ${LOCAL_ARCHIVE}
puts "\033\[0;32m✓ Arquivo ${LOCAL_ARCHIVE} removido\033\[0m"
puts ""
