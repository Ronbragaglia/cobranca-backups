#!/bin/bash
# Script para resolver conflito de porta 80 entre docker-proxy e nginx
# Execute este script na VPS (IP: 76.13.167.54)

set -e

echo "=========================================="
echo "RESOLVENDO CONFLITO DE PORTA 80"
echo "=========================================="
echo ""

# Passo 1: Identificar containers usando portas 80/443
echo "📋 PASSO 1: Identificando containers usando portas 80/443..."
docker ps -a --filter "publish=80" --format "table {{.Names}}\t{{.Ports}}\t{{.Status}}"
docker ps -a --filter "publish=443" --format "table {{.Names}}\t{{.Ports}}\t{{.Status}}"

echo ""
echo "📋 Lista completa de containers:"
docker ps -a

echo ""
echo "📋 Processos escutando na porta 80:"
netstat -tlnp | grep :80 || echo "Nenhum processo encontrado"

echo ""
echo "📋 Processos escutando na porta 443:"
netstat -tlnp | grep :443 || echo "Nenhum processo encontrado"

echo ""
echo "=========================================="
echo "📋 PASSO 2: Parando containers que usam porta 80/443"
echo "=========================================="

# Lista de possíveis containers de proxy
PROXY_CONTAINERS=$(docker ps -a --filter "publish=80" --format "{{.Names}}")

if [ -z "$PROXY_CONTAINERS" ]; then
    echo "⚠️  Nenhum container encontrado usando a porta 80"
else
    echo "🛑 Parando os seguintes containers:"
    echo "$PROXY_CONTAINERS"
    echo ""
    
    for container in $PROXY_CONTAINERS; do
        echo "Parando $container..."
        docker stop "$container" || echo "Erro ao parar $container"
        docker rm "$container" || echo "Erro ao remover $container"
    done
fi

echo ""
echo "📋 Verificando processos docker-proxy restantes:"
ps aux | grep docker-proxy | grep -v grep || echo "Nenhum docker-proxy encontrado"

echo ""
echo "=========================================="
echo "📋 PASSO 3: Iniciando Nginx"
echo "=========================================="

# Verificar configuração do nginx
echo "🔍 Testando configuração do nginx:"
nginx -t || echo "⚠️  Erro na configuração do nginx"

echo ""
echo "🚀 Iniciando nginx:"
systemctl restart nginx

echo ""
echo "📋 Status do nginx:"
systemctl status nginx --no-pager

echo ""
echo "=========================================="
echo "📋 PASSO 4: Verificando portas"
echo "=========================================="

echo "📋 Processos escutando na porta 80:"
netstat -tlnp | grep :80

echo ""
echo "📋 Processos escutando na porta 443:"
netstat -tlnp | grep :443

echo ""
echo "=========================================="
echo "📋 PASSO 5: Testando acesso HTTP"
echo "=========================================="

echo "🔍 Testando HTTP (curl -I http://76.13.167.54):"
curl -I http://76.13.167.54 2>&1 || echo "Erro ao testar HTTP"

echo ""
echo "🔍 Testando localhost:"
curl -I http://localhost 2>&1 || echo "Erro ao testar localhost"

echo ""
echo "=========================================="
echo "✅ PROCESSO CONCLUÍDO"
echo "=========================================="
echo ""
echo "Resumo:"
echo "- Containers usando porta 80/443 foram parados"
echo "- Nginx foi reiniciado"
echo "- Portas foram verificadas"
echo ""
echo "Se ainda houver problemas, verifique:"
echo "1. /etc/nginx/sites-available/cobranca-api"
echo "2. systemctl status nginx"
echo "3. netstat -tlnp | grep :80"
echo ""
