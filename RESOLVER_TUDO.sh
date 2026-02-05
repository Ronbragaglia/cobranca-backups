#!/bin/bash

echo "🚀 Iniciando resolução de todos os problemas..."

echo "📋 Passo 1: Reiniciando o PHP-FPM..."
docker exec cobranca_app killall php-fpm
docker exec cobranca_app php-fpm -D

echo "⏳ Aguardando 5 segundos..."
sleep 5

echo "📋 Passo 2: Verificando se o PHP-FPM está escutando em IPv4..."
docker exec cobranca_app netstat -tlnp | grep 9000

echo "📋 Passo 3: Se ainda estiver em IPv6, modificando o arquivo..."
docker exec cobranca_app sh -c 'cat > /usr/local/etc/php-fpm.d/www.conf << '\''EOF'\''
[www]
user = www-data
group = www-data
listen = 0.0.0.0:9000
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
EOF'

echo "📋 Passo 4: Reiniciando o PHP-FPM novamente..."
docker exec cobranca_app killall php-fpm
docker exec cobranca_app php-fpm -D

echo "⏳ Aguardando 5 segundos..."
sleep 5

echo "📋 Passo 5: Verificando se o PHP-FPM está escutando em IPv4..."
docker exec cobranca_app netstat -tlnp | grep 9000

echo "📋 Passo 6: Testando conexão do Nginx com o PHP-FPM..."
docker exec cobranca_nginx wget -O- http://cobranca_app:9000/health

echo "📋 Passo 7: Testando o site..."
curl -i http://api.cobrancaauto.com.br/

echo "✅ Resolução concluída!"
