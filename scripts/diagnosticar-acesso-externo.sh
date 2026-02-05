#!/bin/bash

################################################################################
# 🔥 DIAGNÓSTICO - ACESSO EXTERNO (SITE FUNCIONA LOCALMENTE MAS NÃO EXTERNAMENTE)
################################################################################

set -e

echo "=========================================="
echo "🔍 DIAGNÓSTICO - ACESSO EXTERNO"
echo "=========================================="
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

################################################################################
# ETAPA 1: VERIFICAR SE SITE FUNCIONA LOCALMENTE
################################################################################
echo -e "${YELLOW}[1/6] VERIFICANDO ACESSO LOCAL${NC}"
echo "----------------------------------------"

echo "📌 Testando localhost:"
curl -I http://localhost/ 2>&1 | head -10
echo ""

echo "📌 Testando 127.0.0.1:"
curl -I http://127.0.0.1/ 2>&1 | head -10
echo ""

################################################################################
# ETAPA 2: VERIFICAR FIREWALL
################################################################################
echo -e "${YELLOW}[2/6] VERIFICANDO FIREWALL${NC}"
echo "----------------------------------------"

echo "📌 Status UFW:"
ufw status verbose || echo "UFW não instalado ou inativo"
echo ""

echo "📌 Regras iptables:"
iptables -L -n -v | grep -E "(Chain|80|443|ACCEPT|DROP)" || echo "Nenhuma regra iptables encontrada"
echo ""

echo "📌 Portas abertas:"
ss -tlnp | grep -E ":(80|443)" || echo "Nenhuma porta 80/443 escutando"
echo ""

################################################################################
# ETAPA 3: VERIFICAR NGINX ESTÁ ESCUTANDO
################################################################################
echo -e "${YELLOW}[3/6] VERIFICANDO NGINX${NC}"
echo "----------------------------------------"

echo "📌 NGINX Status:"
systemctl status nginx --no-pager | head -10
echo ""

echo "📌 NGINX escutando em:"
netstat -tlnp | grep nginx || ss -tlnp | grep nginx
echo ""

echo "📌 Configuração NGINX:"
grep -E "listen|server_name" /etc/nginx/sites-available/cobranca-api
echo ""

################################################################################
# ETAPA 4: VERIFICAR CLOUDFLARE
################################################################################
echo -e "${YELLOW}[4/6] VERIFICANDO CLOUDFLARE${NC}"
echo "----------------------------------------"

echo "📌 Testando DNS:"
dig api.cobrancaauto.com.br +short
echo ""

echo "📌 Testando HTTP externo:"
curl -I http://api.cobrancaauto.com.br/ 2>&1 | head -10
echo ""

echo "📌 Testando IP direto:"
IP_PUBLICO=$(curl -s ifconfig.me)
echo "IP Público: $IP_PUBLICO"
curl -I http://$IP_PUBLICO/ 2>&1 | head -10
echo ""

################################################################################
# ETAPA 5: VERIFICAR LOGS DE ERRO
################################################################################
echo -e "${YELLOW}[5/6] VERIFICANDO LOGS${NC}"
echo "----------------------------------------"

echo "📌 Últimos 20 linhas do NGINX error.log:"
tail -20 /var/log/nginx/error.log
echo ""

echo "📌 Últimas 20 linhas do NGINX access.log:"
tail -20 /var/log/nginx/access.log
echo ""

################################################################################
# ETAPA 6: SOLUÇÃO - ABRIR PORTA 80 NO FIREWALL
################################################################################
echo -e "${YELLOW}[6/6] SOLUÇÃO - ABRIR PORTA 80${NC}"
echo "----------------------------------------"

echo "📌 Habilitando porta 80 no UFW:"
ufw allow 80/tcp || echo "UFW não disponível"
echo ""

echo "📌 Habilitando porta 443 no UFW:"
ufw allow 443/tcp || echo "UFW não disponível"
echo ""

echo "📌 Status UFW após alteração:"
ufw status verbose || echo "UFW não instalado"
echo ""

echo "📌 Reiniciando NGINX:"
systemctl restart nginx
systemctl status nginx --no-pager | head -5
echo ""

################################################################################
# TESTE FINAL
################################################################################
echo "=========================================="
echo -e "${GREEN}✅ DIAGNÓSTICO CONCLUÍDO${NC}"
echo "=========================================="
echo ""
echo "📌 TESTE FINAL:"
echo ""
echo "1. Teste no navegador:"
echo "   http://api.cobrancaauto.com.br"
echo ""
echo "2. Teste via curl:"
echo "   curl -I http://api.cobrancaauto.com.br/"
echo ""
echo "3. Verifique Cloudflare:"
echo "   - Proxy: deve estar OFF (DNS only)"
echo "   - DNS: apontando para IP correto"
echo ""
echo "4. Se ainda não funcionar, verifique:"
echo "   - Firewall do provedor (Hostinger)"
echo "   - DNS propagation"
echo "   - Certificado SSL (se usar HTTPS)"
echo ""
echo "=========================================="
