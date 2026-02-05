#!/bin/bash
# Script de limpeza do projeto cobranca-api
# Autor: Kilo Code
# Data: 05/02/2026

echo "🧹 Iniciando limpeza do projeto cobranca-api..."
echo ""

# Contador de arquivos removidos
ARQUIVOS_REMOVIDOS=0
ESPACO_LIBERADO=0

# Função para remover e contar
remover_arquivo() {
    if [ -e "$1" ]; then
        TAMANHO=$(du -sh "$1" 2>/dev/null | cut -f1)
        rm -rf "$1"
        echo "   ❌ Removido: $1 ($TAMANHO)"
        ((ARQUIVOS_REMOVIDOS++))
    fi
}

# 1. Remover vendor
echo "📦 [1/8] Removendo vendor/..."
remover_arquivo "vendor/"

# 2. Remover logs
echo "📋 [2/8] Removendo storage/logs/..."
remover_arquivo "storage/logs/*"
remover_arquivo "storage/logs/.gitignore"

# 3. Remover cache do framework
echo "💾 [3/8] Limpando cache do framework..."
remover_arquivo "storage/framework/cache/*"
remover_arquivo "storage/framework/sessions/*"
remover_arquivo "storage/framework/views/*"

# 4. Remover arquivo compactado temporário
echo "📦 [4/8] Removendo cobranca-api-completo.tar.gz..."
remover_arquivo "cobranca-api-completo.tar.gz"

# 5. Remover backups antigos
echo "💾 [5/8] Removendo backups/..."
remover_arquivo "backups/"

# 6. Remover documentação duplicada
echo "📚 [6/8] Removendo documentação duplicada..."

# Solução de problemas
for arquivo in SOLUCAO_*.md CORRIGIR_*.md FIX_*.md DIAGNOSTICO_*.md RESOLVER_*.md INVESTIGAR_*.md VERIFICAR_*.md EXPORTE_*.md LIMPAR_*.md PARAR_*.md TRAZER_*.md; do
    remover_arquivo "$arquivo"
done

# Relatórios
for arquivo in RELATORIO_*.md RESUMO_*.md; do
    remover_arquivo "$arquivo"
done

# Instruções de deploy
for arquivo in INSTRUCOES_*.md INSTRUCOES_*.txt GUIA_*.md GUIA_*.txt CONFIGURAR_*.md PREPARACAO_*.md CHECKLIST_*.md DIRETRIZES_*.md; do
    remover_arquivo "$arquivo"
done

# Comandos
for arquivo in COMANDOS_*.md COMANDOS_*.txt ATUALIZAR_*.md SUBSTITUIR_*.md REDEFINIR_*.md REINICIAR_*.md EXECUTAR_*.md; do
    remover_arquivo "$arquivo"
done

# Arquivos temporários
for arquivo in EXECUTE_*.txt EXECUTAR_*.txt OUTPUT_*.txt CONTINUAR_*.txt; do
    remover_arquivo "$arquivo"
done

# Outros
for arquivo in ADICIONAR_*.md CRIAR_*.md MELHORIAS_*.md STATUS_*.md PROXIMOS_*.md; do
    remover_arquivo "$arquivo"
done

# Configurações antigas
for arquivo in nginx-*.txt Caddyfile.* *.sql; do
    remover_arquivo "$arquivo"
done

# 7. Remover arquivos de script temporários
echo "📜 [7/8] Removendo scripts temporários..."
remover_arquivo "COMANDO_ENVIAR_PROJETO_VPS.sh"
remover_arquivo "INSTRUCOES_DOWNLOAD_PROJETO.md"
remover_arquivo "INSTRUCOES_ENVIAR_PROJETO_VPS.md"
remover_arquivo "INSTRUCOES_FINAIS_ENVIO_VPS.md"

# 8. Verificar .gitignore
echo "🔍 [8/8] Verificando .gitignore..."
if [ ! -f ".gitignore" ]; then
    echo "   ⚠️  .gitignore não encontrado, criando..."
    cat > .gitignore << 'EOF'
/vendor
/node_modules
/.idea
/.vscode
/storage/*.key
/storage/*.log
/storage/framework/cache/*
/storage/framework/sessions/*
/storage/framework/views/*
.env
.env.backup
.env.production
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/backups
*.tar.gz
*.sql
EOF
    echo "   ✅ .gitignore criado"
else
    echo "   ✅ .gitignore já existe"
fi

# Resumo
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ LIMPEZA CONCLUÍDA!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📊 Estatísticas:"
echo "   - Arquivos removidos: $ARQUIVOS_REMOVIDOS"
echo ""

# Calcular tamanho final
TAMANHO_FINAL=$(du -sh . 2>/dev/null | cut -f1)
echo "   - Tamanho final do projeto: $TAMANHO_FINAL"
echo ""

# Listar arquivos essenciais mantidos
echo "📚 Documentação essencial mantida:"
echo "   ✅ README.md"
echo "   ✅ ROADMAP_TECNICO.md"
echo "   ✅ AUDITORIA_SEGURANCA_COMPLETA.md"
echo "   ✅ CREDENCIAIS_ACESSO.md"
echo "   ✅ CREDENCIAIS_VPS.md"
echo "   ✅ INSTRUCOES_DOWNLOAD_GITHUB.md"
echo ""

echo "🚀 Próximos passos:"
echo "   1. Verifique se o projeto ainda funciona"
echo "   2. Execute: composer install (para reinstalar dependências)"
echo "   3. Execute: cd frontend && npm install (para instalar dependências frontend)"
echo "   4. Execute: php artisan key:generate"
echo "   5. Execute: php artisan migrate"
echo ""

echo "💚 Projeto limpo e pronto para uso! 💸"
