#!/bin/bash

# Script para resolver o problema da porta 9000 e trazer o site de volta

echo "=== PARANDO CONTAINER APP ==="
docker stop cobranca_app

echo "=== REMOVENDO CONTAINER APP ==="
docker rm cobranca_app

echo "=== CRIANDO NOVO CONTAINER COM PORTA EXPOSTA ==="
docker run -d --name cobranca_app --restart unless-stopped --network cobranca-api_cobranca_network -p 127.0.0.1:9000:9000 -v /var/www/cobranca-api/storage:/var/www/storage cobranca-api_app php-fpm

echo "=== AGUARDANDO 10 SEGUNDOS ==="
sleep 10

echo "=== VERIFICANDO SE A PORTA ESTÁ EXPOSTA ==="
docker port cobranca_app

echo "=== TESTANDO CONEXÃO ==="
curl -I http://127.0.0.1:9000

echo "=== TESTANDO HEALTH CHECK ==="
curl https://api.cobrancaauto.com.br/health

echo "=== TESTANDO SITE ==="
curl https://api.cobrancaauto.com.br/

echo "=== CONCLUÍDO ==="
