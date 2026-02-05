#!/bin/bash

# ============================================
# 🌐 Script de Configuração Nginx Proxy Reverso
# ============================================
# Este script configura o Nginx como proxy reverso para Laravel
# Servindo na porta 80/443 (HTTP/HTTPS) e redirecionando
# para o PHP-FPM na porta 8082 (interna)
#
# Uso: Copiar e colar este script na VPS
# ============================================

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}🌐 Configuração Nginx Proxy Reverso${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}❌ Este script precisa ser executado como root${NC}"
    exit 1
fi

# Verificar se o Nginx está instalado
if ! command -v nginx &> /dev/null; then
    echo -e "${YELLOW}📦 Instalando Nginx...${NC}"
    apt update
    apt install nginx -y
    echo -e "${GREEN}✅ Nginx instalado com sucesso!${NC}"
else
    echo -e "${GREEN}✅ Nginx já está instalado${NC}"
fi
echo ""

# Criar configuração do Nginx
echo -e "${BLUE}📝 Criando configuração do Nginx...${NC}"
cat > /etc/nginx/sites-available/cobranca-api << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name 76.13.167.54 _;

    root /root/cobranca-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php index.html index.htm;

    charset utf-8;

    # Logs
    access_log /var/log/nginx/cobranca-api-access.log;
    error_log /var/log/nginx/cobranca-api-error.log;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /index.php {
        # Passar para PHP-FPM na porta 8082 (interna)
        fastcgi_pass 127.0.0.1:8082;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param PATH_INFO \$fastcgi_path_info;

        # Timeout aumentado para Laravel
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Bloquear acesso a arquivos ocultos
    location ~ /\. {
        deny all;
    }

    # Configurações de upload (para arquivos grandes)
    client_max_body_size 100M;
    fastcgi_buffer_size 128k;
    fastcgi_buffers 4 256k;
}
EOF

echo -e "${GREEN}✅ Configuração criada em /etc/nginx/sites-available/cobranca-api${NC}"
echo ""

# Criar link simbólico para sites-enabled
echo -e "${BLUE}🔗 Ativando site...${NC}"
ln -sf /etc/nginx/sites-available/cobranca-api /etc/nginx/sites-enabled/cobranca-api
echo -e "${GREEN}✅ Site ativado${NC}"
echo ""

# Remover configuração padrão do Nginx
echo -e "${BLUE}🗄️ Removendo configuração padrão...${NC}"
rm -f /etc/nginx/sites-enabled/default
echo -e "${GREEN}✅ Configuração padrão removida${NC}"
echo ""

# Testar configuração do Nginx
echo -e "${BLUE}🧪 Testando configuração do Nginx...${NC}"
nginx -t

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Configuração do Nginx está correta!${NC}"
else
    echo -e "${RED}❌ Erro na configuração do Nginx!${NC}"
    exit 1
fi
echo ""

# Reiniciar Nginx
echo -e "${BLUE}🔄 Reiniciando Nginx...${NC}"
systemctl restart nginx

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Nginx reiniciado com sucesso!${NC}"
else
    echo -e "${RED}❌ Erro ao reiniciar Nginx!${NC}"
    exit 1
fi
echo ""

# Verificar se o Nginx está rodando
echo -e "${BLUE}📊 Verificando status do Nginx...${NC}"
systemctl status nginx --no-pager
echo ""

# Verificar se o PHP-FPM está rodando na porta 8082
echo -e "${BLUE}📊 Verificando PHP-FPM na porta 8082...${NC}"
netstat -tlnp | grep :8082 || ss -tlnp | grep :8082

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ PHP-FPM está rodando na porta 8082${NC}"
else
    echo -e "${RED}❌ PHP-FPM não está rodando na porta 8082!${NC}"
    echo -e "${YELLOW}💡 Verifique se o Laravel está rodando: php8.2 artisan serve --host=0.0.0.0 --port=8082${NC}"
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Configuração concluída!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${BLUE}📋 Próximos passos:${NC}"
echo ""
echo -e "${YELLOW}1. A aplicação deve estar acessível em:${NC}"
echo -e "   ${GREEN}http://76.13.167.54${NC}"
echo ""
echo -e "${YELLOW}2. Para configurar HTTPS (SSL):${NC}"
echo -e "   - Instalar Certbot: ${GREEN}apt install certbot python3-certbot-nginx -y${NC}"
echo -e "   - Obter certificado: ${GREEN}certbot --nginx -d 76.13.167.54${NC}"
echo ""
echo -e "${YELLOW}3. Verificar logs do Nginx:${NC}"
echo -e "   - Acesso: ${GREEN}tail -f /var/log/nginx/cobranca-api-access.log${NC}"
echo -e "   - Erro: ${GREEN}tail -f /var/log/nginx/cobranca-api-error.log${NC}"
echo ""
