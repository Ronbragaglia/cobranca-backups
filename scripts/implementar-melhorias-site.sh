#!/bin/bash

################################################################################
# 🔧 IMPLEMENTAR MELHORIAS NECESSÁRIAS PARA O SITE
################################################################################

set -e

echo "=========================================="
echo "🔧 IMPLEMENTANDO MELHORIAS PARA O SITE"
echo "=========================================="
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Diretório do projeto
PROJECT_DIR="/var/www/cobranca-api"

################################################################################
# ETAPA 1: CRIAR CONTROLLER DE CONFIGURAÇÕES DE VENDAS
################################################################################

echo -e "${YELLOW}[1/5] CRIANDO CONTROLLER DE CONFIGURAÇÕES DE VENDAS${NC}"
echo "----------------------------------------"

cat > ${PROJECT_DIR}/app/Http/Controllers/SalesSettingsController.php << 'CONTROLLEREOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SalesSettingsController extends Controller
{
    /**
     * Exibir configurações de vendas
     */
    public function index()
    {
        $whatsappNumber = env('WHATSAPP_SALES_NUMBER', '');
        $whatsappMessage = env('WHATSAPP_SALES_MESSAGE', '');
        
        return view('sales-settings', compact('whatsappNumber', 'whatsappMessage'));
    }

    /**
     * Atualizar configurações de vendas
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_number' => 'required|string|max:20',
            'whatsapp_message' => 'required|string|max:500',
        ]);

        // Atualizar .env
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        $envContent = preg_replace(
            '/^WHATSAPP_SALES_NUMBER=.*/m',
            "WHATSAPP_SALES_NUMBER={$validated['whatsapp_number']}",
            $envContent
        );
        
        $envContent = preg_replace(
            '/^WHATSAPP_SALES_MESSAGE=.*/m',
            "WHATSAPP_SALES_MESSAGE=\"{$validated['whatsapp_message']}\"",
            $envContent
        );
        
        file_put_contents($envFile, $envContent);

        // Limpar cache
        Cache::flush();

        return redirect()->back()->with('success', 'Configurações atualizadas com sucesso!');
    }
}
CONTROLLEREOF

echo -e "${GREEN}✅ Controller criado${NC}"
echo ""

################################################################################
# ETAPA 2: CRIAR VIEW DE CONFIGURAÇÕES DE VENDAS
################################################################################

echo -e "${YELLOW}[2/5] CRIANDO VIEW DE CONFIGURAÇÕES DE VENDAS${NC}"
echo "----------------------------------------"

cat > ${PROJECT_DIR}/resources/views/sales-settings.blade.php << 'VIEWEOF'
@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">Configurações de Vendas</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('sales-settings.update') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Número de WhatsApp</label>
            <input 
                type="text" 
                name="whatsapp_number" 
                value="{{ old('whatsapp_number', $whatsappNumber) }}"
                class="w-full px-3 py-2 border rounded-md"
                placeholder="5511999999999"
                required
            >
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Mensagem Padrão</label>
            <textarea 
                name="whatsapp_message" 
                rows="4"
                class="w-full px-3 py-2 border rounded-md"
                required
            >{{ old('whatsapp_message', $whatsappMessage) }}</textarea>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
            Salvar Configurações
        </button>
    </form>
</div>
@endsection
VIEWEOF

echo -e "${GREEN}✅ View criada${NC}"
echo ""

################################################################################
# ETAPA 3: ADICIONAR ROTAS DE CONFIGURAÇÕES DE VENDAS
################################################################################

echo -e "${YELLOW}[3/5] ADICIONANDO ROTAS DE CONFIGURAÇÕES DE VENDAS${NC}"
echo "----------------------------------------"

# Backup do arquivo de rotas
cp ${PROJECT_DIR}/routes/web.php ${PROJECT_DIR}/routes/web.php.backup.$(date +%Y%m%d_%H%M%S)

# Adicionar rotas
if ! grep -q "SalesSettingsController" ${PROJECT_DIR}/routes/web.php; then
    echo "Adicionando rotas de configurações de vendas..."
    
    # Adicionar use do controller
    sed -i '/use App\\\\Http\\\\Controllers\\\\ProfileController;/a use App\\\\Http\\\\Controllers\\\\SalesSettingsController;' ${PROJECT_DIR}/routes/web.php
    
    # Adicionar rotas antes do require
    sed -i '/^Route::middleware/i\
// Sales Settings\
Route::get('"'"'"'/sales-settings'"'"', [SalesSettingsController::class, '"'"'"'index'"'"'])\
    ->middleware(['"'"'"'auth'"'"'])\
    ->name('"'"'"'sales-settings.index'"'"');\
\
Route::put('"'"'"'/sales-settings'"'"', [SalesSettingsController::class, '"'"'"'update'"'"'])\
    ->middleware(['"'"'"'auth'"'"'])\
    ->name('"'"'"'sales-settings.update'"'"');\
\
' ${PROJECT_DIR}/routes/web.php
    
    echo -e "${GREEN}✅ Rotas adicionadas${NC}"
else
    echo -e "${GREEN}✅ Rotas já existem${NC}"
fi

echo ""

################################################################################
# ETAPA 4: ATUALIZAR PÁGINA INICIAL COM NÚMERO DE WHATSAPP
################################################################################

echo -e "${YELLOW}[4/5] ATUALIZANDO PÁGINA INICIAL${NC}"
echo "----------------------------------------"

# Verificar se existe arquivo landing.blade.php
if [ -f "${PROJECT_DIR}/resources/views/landing.blade.php" ]; then
    # Backup do arquivo
    cp ${PROJECT_DIR}/resources/views/landing.blade.php ${PROJECT_DIR}/resources/views/landing.blade.php.backup.$(date +%Y%m%d_%H%M%S)
    
    # Atualizar link de WhatsApp
    sed -i 's|https://wa.me/qr/5UYGQNHBGZVKC1|https://wa.me/{{ env("WHATSAPP_SALES_NUMBER", "5511999999999") }}|g' ${PROJECT_DIR}/resources/views/landing.blade.php
    
    # Atualizar QR Code
    sed -i 's|data=https://wa.me/qr/5UYGQNHBGZVKC1|data=https://wa.me/{{ env("WHATSAPP_SALES_NUMBER", "5511999999999") }}|g' ${PROJECT_DIR}/resources/views/landing.blade.php
    
    echo -e "${GREEN}✅ Página inicial atualizada${NC}"
else
    echo -e "${YELLOW}⚠️ Arquivo landing.blade.php não encontrado${NC}"
fi

echo ""

################################################################################
# ETAPA 5: ADICIONAR CONFIGURAÇÕES AO .ENV
################################################################################

echo -e "${YELLOW}[5/5] ADICIONANDO CONFIGURAÇÕES AO .ENV${NC}"
echo "----------------------------------------"

# Backup do arquivo .env
cp ${PROJECT_DIR}/.env ${PROJECT_DIR}/.env.backup.$(date +%Y%m%d_%H%M%S)

# Adicionar configurações ao .env
if ! grep -q "WHATSAPP_SALES_NUMBER" ${PROJECT_DIR}/.env; then
    echo "Adicionando configurações de WhatsApp ao .env..."
    
    cat >> ${PROJECT_DIR}/.env << 'ENVEOF'

# WhatsApp Sales Settings
WHATSAPP_SALES_NUMBER=5511999999999
WHATSAPP_SALES_MESSAGE="Olá! Gostaria de saber mais sobre o sistema de cobrança automática."
ENVEOF
    
    echo -e "${GREEN}✅ Configurações adicionadas ao .env${NC}"
else
    echo -e "${GREEN}✅ Configurações já existem no .env${NC}"
fi

echo ""

################################################################################
# ETAPA 6: LIMPAR CACHE
################################################################################

echo -e "${YELLOW}[6/6] LIMPANDO CACHE${NC}"
echo "----------------------------------------"

cd ${PROJECT_DIR}
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo -e "${GREEN}✅ Cache limpo${NC}"
echo ""

################################################################################
# RESUMO FINAL
################################################################################

echo "=========================================="
echo -e "${GREEN}✅ MELHORIAS IMPLEMENTADAS!${NC}"
echo "=========================================="
echo ""
echo "📋 MELHORIAS IMPLEMENTADAS:"
echo ""
echo "1. ✅ Controller SalesSettingsController criado"
echo "2. ✅ View sales-settings.blade.php criada"
echo "3. ✅ Rotas de configurações de vendas adicionadas"
echo "4. ✅ Página inicial atualizada com número de WhatsApp"
echo "5. ✅ Configurações de WhatsApp adicionadas ao .env"
echo "6. ✅ Cache limpo"
echo ""
echo "📋 PRÓXIMOS PASSOS:"
echo ""
echo "1. Acessar: https://api.cobrancaauto.com.br/sales-settings"
echo "2. Configurar número de WhatsApp do time de vendas"
echo "3. Configurar mensagem padrão"
echo "4. Testar link de WhatsApp na página inicial"
echo "5. Testar QR Code na página inicial"
echo ""
echo "📋 COMANDOS ÚTEIS:"
echo ""
echo "# Acessar configurações de vendas"
echo "https://api.cobrancaauto.com.br/sales-settings"
echo ""
echo "# Verificar configurações no .env"
echo "grep WHATSAPP_SALES /var/www/cobranca-api/.env"
echo ""
echo "# Limpar cache"
echo "cd /var/www/cobranca-api"
echo "php artisan config:clear"
echo "php artisan cache:clear"
echo ""
echo "=========================================="
echo -e "${GREEN}💚 MELHORIAS IMPLEMENTADAS!${NC}"
echo "=========================================="
