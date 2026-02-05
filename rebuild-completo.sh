#!/bin/bash

# Script para rebuild completo dos containers Docker
# Isso garante que todas as mudanças no docker-compose.prod.yml sejam aplicadas

echo "🚀 Iniciando rebuild completo..."
echo ""

# 1. Parar e remover todos os containers
echo "📦 Parando containers..."
docker-compose -f docker-compose.prod.yml down -v

# 2. Remover imagens antigas (força rebuild)
echo "🗑️ Removendo imagens antigas..."
docker-compose -f docker-compose.prod.yml build --no-cache --pull

# 3. Subir containers com nova configuração
echo "🚀 Subindo containers..."
docker-compose -f docker-compose.prod.yml up -d

# 4. Aguardar containers iniciarem
echo "⏳ Aguardando containers iniciarem (30 segundos)..."
sleep 30

# 5. Verificar status dos containers
echo ""
echo "📊 Status dos containers:"
docker-compose -f docker-compose.prod.yml ps

echo ""
echo "🔍 Verificando se a porta 9000 está exposta..."
netstat -tlnp | grep 9000

echo ""
echo "✅ Rebuild completo!"
echo ""
echo "📝 Próximos passos:"
echo "1. Testar health check: curl https://api.cobrancaauto.com.br/health"
echo "2. Verificar logs: docker-compose -f docker-compose.prod.yml logs app"
echo "3. Configurar Nginx (se ainda não estiver configurado)"
