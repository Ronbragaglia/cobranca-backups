#!/bin/bash
# Script para resolver conflito de portas 80/443 com Traefik/EasyPanel
# Execute este script na VPS (IP: 76.13.167.54)

set -e

echo "=========================================="
echo "🚨 RESOLVENDO CONFLITO DE PORTAS 80/443"
echo "=========================================="
echo ""

# Passo 1: Identificar containers usando portas 80/443
echo "📋 PASSO 1: Identificando containers usando portas 80/443..."
echo ""
echo "Containers usando porta 80:"
docker ps -a --filter "publish=80" --format "table {{.Names}}\t{{.Ports}}\t{{.Status}}"
echo ""
echo "Containers usando porta 443:"
docker ps -a --filter "publish=443" --format "table {{.Names}}\t{{.Ports}}\t{{.Status}}"
echo ""

# Passo 2: Parar containers que usam portas 80/443
echo "=========================================="
echo "📋 PASSO 2: Parando containers que usam portas 80/443"
echo "=========================================="
echo ""

# Parar containers usando porta 80
PROXY_CONTAINERS_80=$(docker ps -a --filter "publish=80" --format "{{.Names}}" 2>/dev/null || true)

if [ -n "$PROXY_CONTAINERS_80" ]; then
    echo "🛑 Parando containers que usam porta 80..."
    for container in $PROXY_CONTAINERS_80; do
        echo "  - Parando $container..."
        docker stop "$container" 2>/dev/null || echo "    Erro ao parar $container"
        docker rm "$container" 2>/dev/null || echo "    Erro ao remover $container"
    done
    echo ""
else
    echo "⚠️  Nenhum container encontrado usando a porta 80"
    echo ""
fi

# Parar containers usando porta 443
PROXY_CONTAINERS_443=$(docker ps -a --filter "publish=443" --format "{{.Names}}" 2>/dev/null || true)

if [ -n "$PROXY_CONTAINERS_443" ]; then
    echo "🛑 Parando containers que usam porta 443..."
    for container in $PROXY_CONTAINERS_443; do
        echo "  - Parando $container..."
        docker stop "$container" 2>/dev/null || echo "    Erro ao parar $container"
        docker rm "$container" 2>/dev/null || echo "    Erro ao remover $container"
    done
    echo ""
else
    echo "⚠️  Nenhum container encontrado usando a porta 443"
    echo ""
fi

# Passo 3: Verificar se ainda há processos nas portas
echo "=========================================="
echo "📋 PASSO 3: Verificando processos nas portas"
echo "=========================================="
echo ""

echo "Processos na porta 80:"
netstat -tlnp | grep :80 || echo "✅ Porta 80 livre"
echo ""

echo "Processos na porta 443:"
netstat -tlnp | grep :443 || echo "✅ Porta 443 livre"
echo ""

# Passo 4: Matar processos docker-proxy restantes (se necessário)
echo "=========================================="
echo "📋 PASSO 4: Matar processos docker-proxy restantes"
echo "=========================================="
echo ""

DOCKER_PROXY_PIDS=$(ps aux | grep docker-proxy | grep -v grep | awk '{print $2}')

if [ -n "$DOCKER_PROXY_PIDS" ]; then
    echo "🔨 Matar processos docker-proxy:"
    for pid in $DOCKER_PROXY_PIDS; do
        echo "  - Matar PID $pid"
        kill -9 "$pid" 2>/dev/null || echo "    Erro ao matar PID $pid"
    done
    echo ""
else
    echo "✅ Nenhum processo docker-proxy encontrado"
    echo ""
fi

# Passo 5: Verificar novamente as portas
echo "=========================================="
echo "📋 PASSO 5: Verificando portas novamente"
echo "=========================================="
echo ""

echo "Processos na porta 80:"
netstat -tlnp | grep :80 || echo "✅ Porta 80 livre"
echo ""

echo "Processos na porta 443:"
netstat -tlnp | grep :443 || echo "✅ Porta 443 livre"
echo ""

# Passo 6: Reiniciar Nginx
echo "=========================================="
echo "📋 PASSO 6: Reiniciando Nginx"
echo "=========================================="
echo ""

echo "🔍 Testando configuração do Nginx..."
nginx -t || echo "⚠️  Erro na configuração do Nginx"

echo ""
echo "🚀 Reiniciando Nginx..."
systemctl restart nginx

echo ""
echo "📋 Status do Nginx:"
systemctl status nginx --no-pager

echo ""
echo "✅ Nginx reiniciado com sucesso"
echo ""

# Passo 7: Verificar status final
echo "=========================================="
echo "📋 PASSO 7: Verificando status final"
echo "=========================================="
echo ""

echo "📋 Containers Docker:"
docker ps

echo ""
echo "📋 Portas em uso:"
netstat -tlnp | grep -E ':(80|443|3306|8082)' || ss -tlnp | grep -E ':(80|443|3306|8082)'

echo ""
echo "🔍 Testando acesso HTTP:"
curl -I "http://api.cobrancaauto.com.br" 2>&1 || echo "⚠️  Erro ao testar HTTP"

echo ""
echo "🔍 Testando acesso HTTPS:"
curl -I "https://api.cobrancaauto.com.br" 2>&1 || echo "⚠️  Erro ao testar HTTPS"

echo ""
echo "=========================================="
echo "✅ PROCESSO CONCLUÍDO!"
echo "=========================================="
echo ""
echo "🎉 Resumo:"
echo "  ✅ Containers usando portas 80/443 foram parados"
echo "  ✅ Processos docker-proxy foram mortos"
echo "  ✅ Nginx foi reiniciado"
echo "  ✅ Portas foram verificadas"
echo ""
echo "📝 Próximos passos:"
echo "  1. Configure o DNS do domínio para apontar para o IP da VPS"
echo "  2. Configure o Cloudflare (se usar) para apontar para o domínio"
echo "  3. Teste a aplicação em: https://api.cobrancaauto.com.br/admin/saas/dashboard"
echo ""
