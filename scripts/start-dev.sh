#!/bin/bash

# Script para iniciar o ambiente de desenvolvimento local do CobrançaAuto SaaS
# Este script inicia todos os serviços Docker necessários para desenvolvimento

set -e

echo "=========================================="
echo "Iniciando Ambiente de Desenvolvimento"
echo "CobrançaAuto SaaS"
echo "=========================================="
echo ""

# Verificar se Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ ERRO: Docker não está instalado"
    echo "Por favor, instale o Docker em: https://docs.docker.com/get-docker/"
    exit 1
fi

# Verificar se Docker Compose está instalado
if ! command -v docker-compose &> /dev/null; then
    echo "❌ ERRO: Docker Compose não está instalado"
    echo "Por favor, instale o Docker Compose em: https://docs.docker.com/compose/install/"
    exit 1
fi

echo "✅ Docker e Docker Compose estão instalados"
echo ""

# Verificar se .env.local existe
if [ ! -f .env.local ]; then
    echo "⚠️  Arquivo .env.local não encontrado"
    echo "Criando arquivo .env.local a partir do modelo..."
    
    if [ -f .env.example ]; then
        cp .env.example .env.local
        echo "✅ Arquivo .env.local criado"
        echo ""
        echo "⚠️  IMPORTANTE: Edite o arquivo .env.local e configure:"
        echo "   - DB_HOST=mysql"
        echo "   - DB_DATABASE=cobranca"
        echo "   - DB_USERNAME=cobranca"
        echo "   - DB_PASSWORD=__SET_IN_SERVER_ONLY__"
        echo ""
        read -p "Pressione ENTER para continuar após editar o .env.local..."
    else
        echo "❌ ERRO: Arquivo .env.example não encontrado"
        exit 1
    fi
fi

# Copiar .env.local para .env (para o Laravel)
echo "📝 Configurando ambiente Laravel..."
cp .env.local .env
echo "✅ Arquivo .env configurado"
echo ""

# Gerar APP_KEY se necessário
if grep -q "YOUR_APP_KEY_HERE" .env; then
    echo "🔑 Gerando APP_KEY..."
    php artisan key:generate
    echo "✅ APP_KEY gerada"
    echo ""
fi

# Parar containers antigos se existirem
echo "🛑 Parando containers antigos..."
docker-compose -f docker-compose.dev.yml down 2>/dev/null || true
echo "✅ Containers antigos parados"
echo ""

# Iniciar containers
echo "🚀 Iniciando containers Docker..."
docker-compose -f docker-compose.dev.yml up -d
echo "✅ Containers iniciados"
echo ""

# Aguardar MySQL estar pronto
echo "⏳ Aguardando MySQL iniciar..."
sleep 10

# Verificar se o MySQL está pronto
echo "🔍 Verificando conexão com MySQL..."
MAX_RETRIES=30
RETRY_COUNT=0

while [ $RETRY_COUNT -lt $MAX_RETRIES ]; do
    if docker-compose -f docker-compose.dev.yml exec -T mysql mysqladmin ping -h localhost -u root -proot &> /dev/null; then
        echo "✅ MySQL está pronto!"
        break
    fi
    RETRY_COUNT=$((RETRY_COUNT + 1))
    echo "   Aguardando MySQL... ($RETRY_COUNT/$MAX_RETRIES)"
    sleep 2
done

if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
    echo "❌ ERRO: MySQL não iniciou corretamente"
    echo "Verifique os logs: docker-compose -f docker-compose.dev.yml logs mysql"
    exit 1
fi

echo ""

# Instalar dependências PHP
echo "📦 Instalando dependências PHP..."
docker-compose -f docker-compose.dev.yml exec -T app composer install --no-interaction
echo "✅ Dependências PHP instaladas"
echo ""

# Instalar dependências Node
echo "📦 Instalando dependências Node..."
docker-compose -f docker-compose.dev.yml exec -T app npm install
echo "✅ Dependências Node instaladas"
echo ""

# Build assets
echo "🔨 Compilando assets..."
docker-compose -f docker-compose.dev.yml exec -T app npm run build
echo "✅ Assets compilados"
echo ""

# Executar migrations
echo "🗄️  Executando migrations..."
docker-compose -f docker-compose.dev.yml exec -T app php artisan migrate --force
echo "✅ Migrations executadas"
echo ""

# Executar seeders
echo "🌱 Executando seeders..."
docker-compose -f docker-compose.dev.yml exec -T app php artisan db:seed --force
echo "✅ Seeders executados"
echo ""

# Limpar cache
echo "🧹 Limpando cache..."
docker-compose -f docker-compose.dev.yml exec -T app php artisan cache:clear
docker-compose -f docker-compose.dev.yml exec -T app php artisan config:clear
docker-compose -f docker-compose.dev.yml exec -T app php artisan route:clear
docker-compose -f docker-compose.dev.yml exec -T app php artisan view:clear
echo "✅ Cache limpo"
echo ""

# Criar storage link
echo "🔗 Criando storage link..."
docker-compose -f docker-compose.dev.yml exec -T app php artisan storage:link
echo "✅ Storage link criado"
echo ""

# Dar permissões
echo "🔐 Configurando permissões..."
docker-compose -f docker-compose.dev.yml exec -T app chown -R www-data:www-data storage bootstrap/cache
docker-compose -f docker-compose.dev.yml exec -T app chmod -R 775 storage bootstrap/cache
echo "✅ Permissões configuradas"
echo ""

echo "=========================================="
echo "✅ Ambiente de desenvolvimento iniciado!"
echo "=========================================="
echo ""
echo "📌 URLs de Acesso:"
echo "   • Aplicação:     http://localhost:8000"
echo "   • API:           http://localhost:8000/api"
echo "   • phpMyAdmin:    http://localhost:8080"
echo ""
echo "📌 Credenciais phpMyAdmin:"
echo "   • Usuário:       root"
echo "   • Senha:         root"
echo ""
echo "📌 Credenciais MySQL:"
echo "   • Host:          localhost:3306"
echo "   • Banco:         cobranca"
echo "   • Usuário:       cobranca"
echo "   • Senha:         cobranca"
echo ""
echo "📌 Comandos Úteis:"
echo "   • Ver logs:      docker-compose -f docker-compose.dev.yml logs -f"
echo "   • Parar tudo:    docker-compose -f docker-compose.dev.yml down"
echo "   • Reiniciar:     docker-compose -f docker-compose.dev.yml restart"
echo "   • Executar PHP:  docker-compose -f docker-compose.dev.yml exec app php artisan ..."
echo "   • Executar Bash:  docker-compose -f docker-compose.dev.yml exec app bash"
echo ""
echo "📌 Para testar as APIs:"
echo "   • Importe a coleção do Insomnia: docs/insomnia-collection.json"
echo "   • Ou use os exemplos em docs/API_EXAMPLES.md"
echo ""
echo "=========================================="
echo "🎉 Bom desenvolvimento!"
echo "=========================================="
