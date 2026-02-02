#!/bin/bash

# Script para configurar MySQL via Docker para o projeto Cobrança API

echo "=========================================="
echo "Setup MySQL via Docker - Cobrança API"
echo "=========================================="
echo ""

# Verifica se o Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Docker não está instalado. Por favor, instale o Docker primeiro."
    exit 1
fi

# Verifica se o Docker Compose está instalado
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose não está instalado. Por favor, instale o Docker Compose primeiro."
    exit 1
fi

echo "✅ Docker e Docker Compose estão instalados"
echo ""

# Pergunta se quer criar/atualizar o .env
if [ -f .env ]; then
    echo "📄 Arquivo .env encontrado"
    read -p "Deseja atualizar o .env para usar MySQL? (s/n): " update_env
    if [ "$update_env" = "s" ] || [ "$update_env" = "S" ]; then
        # Backup do .env atual
        cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
        echo "✅ Backup do .env criado: .env.backup.$(date +%Y%m%d_%H%M%S)"
        
        # Atualiza as configurações do banco de dados
        sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
        sed -i 's/^# DB_HOST=.*/DB_HOST=127.0.0.1/' .env
        sed -i 's/^# DB_PORT=.*/DB_PORT=3306/' .env
        sed -i 's/^# DB_DATABASE=.*/DB_DATABASE=cobranca/' .env
        sed -i 's/^# DB_USERNAME=.*/DB_USERNAME=cobranca/' .env
        sed -i 's/^# DB_PASSWORD=.*/DB_PASSWORD=__SET_IN_SERVER_ONLY__/' .env
        
        # Remove comentários das linhas DB_ se existirem
        sed -i 's/^# DB_CONNECTION=/DB_CONNECTION=/' .env
        sed -i 's/^# DB_HOST=/DB_HOST=/' .env
        sed -i 's/^# DB_PORT=/DB_PORT=/' .env
        sed -i 's/^# DB_DATABASE=/DB_DATABASE=/' .env
        sed -i 's/^# DB_USERNAME=/DB_USERNAME=/' .env
        sed -i 's/^# DB_PASSWORD=/DB_PASSWORD=/' .env
        
        echo "✅ .env atualizado para usar MySQL"
    else
        echo "⚠️  .env não foi atualizado"
    fi
else
    echo "📄 Arquivo .env não encontrado"
    read -p "Deseja criar um .env com configurações MySQL? (s/n): " create_env
    if [ "$create_env" = "s" ] || [ "$create_env" = "S" ]; then
        if [ -f .env.example ]; then
            cp .env.example .env
            # Atualiza as configurações do banco de dados
            sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
            sed -i 's/^# DB_HOST=.*/DB_HOST=127.0.0.1/' .env
            sed -i 's/^# DB_PORT=.*/DB_PORT=3306/' .env
            sed -i 's/^# DB_DATABASE=.*/DB_DATABASE=cobranca/' .env
            sed -i 's/^# DB_USERNAME=.*/DB_USERNAME=cobranca/' .env
            sed -i 's/^# DB_PASSWORD=.*/DB_PASSWORD=__SET_IN_SERVER_ONLY__/' .env
            
            # Remove comentários das linhas DB_ se existirem
            sed -i 's/^# DB_CONNECTION=/DB_CONNECTION=/' .env
            sed -i 's/^# DB_HOST=/DB_HOST=/' .env
            sed -i 's/^# DB_PORT=/DB_PORT=/' .env
            sed -i 's/^# DB_DATABASE=/DB_DATABASE=/' .env
            sed -i 's/^# DB_USERNAME=/DB_USERNAME=/' .env
            sed -i 's/^# DB_PASSWORD=/DB_PASSWORD=/' .env
            
            # Gera a APP_KEY
            php artisan key:generate
            
            echo "✅ .env criado com configurações MySQL"
        else
            echo "❌ Arquivo .env.example não encontrado"
            exit 1
        fi
    else
        echo "⚠️  .env não foi criado"
    fi
fi

echo ""
echo "🚀 Iniciando containers MySQL..."
docker-compose -f docker-compose.mysql.yml up -d

echo ""
echo "⏳ Aguardando o MySQL ficar pronto..."
sleep 10

# Verifica se o MySQL está rodando
if docker ps | grep -q cobranca_mysql; then
    echo "✅ MySQL está rodando"
else
    echo "❌ MySQL não está rodando. Verifique os logs:"
    docker-compose -f docker-compose.mysql.yml logs mysql
    exit 1
fi

echo ""
echo "=========================================="
echo "✅ Setup concluído com sucesso!"
echo "=========================================="
echo ""
echo "📊 Credenciais do MySQL:"
echo "   Host: 127.0.0.1"
echo "   Porta: 3306"
echo "   Banco: cobranca"
echo "   Usuário: cobranca"
echo "   Senha: cobranca"
echo ""
echo "🌐 phpMyAdmin: http://localhost:8080"
echo "   Usuário: root"
echo "   Senha: root"
echo ""
echo "📝 Próximos passos:"
echo "   1. Rodar migrations: php artisan migrate"
echo "   2. Rodar seeders: php artisan db:seed"
echo "   3. Acessar phpMyAdmin: http://localhost:8080"
echo ""
echo "📖 Para mais informações, consulte: README_MYSQL_DOCKER.md"
echo ""
