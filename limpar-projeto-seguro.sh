#!/bin/bash
# Script de limpeza SEGURA do projeto cobranca-api
# NÃO altera o código fonte principal
# Autor: Kilo Code
# Data: 05/02/2026

echo "🧹 Iniciando limpeza SEGURA do projeto cobranca-api..."
echo "⚠️  Código fonte principal NÃO será alterado"
echo ""

# Contador de arquivos removidos
ARQUIVOS_REMOVIDOS=0

# Função para remover e contar
remover_arquivo() {
    if [ -e "$1" ]; then
        TAMANHO=$(du -sh "$1" 2>/dev/null | cut -f1)
        rm -rf "$1"
        echo "   ❌ Removido: $1 ($TAMANHO)"
        ((ARQUIVOS_REMOVIDOS++))
    fi
}

# 1. Limpar logs APENAS
echo "📋 [1/4] Limpando storage/logs/..."
remover_arquivo "storage/logs/*"
remover_arquivo "storage/logs/.gitignore"

# 2. Remover arquivo compactado temporário
echo "📦 [2/4] Removendo cobranca-api-completo.tar.gz..."
remover_arquivo "cobranca-api-completo.tar.gz"

# 3. Remover backups antigos
echo "💾 [3/4] Removendo backups/..."
remover_arquivo "backups/"

# 4. Remover documentação duplicada
echo "📚 [4/4] Removendo documentação duplicada..."

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

# Resumo
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ LIMPEZA SEGURA CONCLUÍDA!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📊 O que foi removido:"
echo "   ❌ storage/logs/* (logs temporários)"
echo "   ❌ cobranca-api-completo.tar.gz (backup temporário)"
echo "   ❌ backups/ (backup antigo)"
echo "   ❌ Documentação duplicada (~100 arquivos)"
echo "   - Total de arquivos removidos: $ARQUIVOS_REMOVIDOS"
echo ""

# Calcular tamanho final
TAMANHO_FINAL=$(du -sh . 2>/dev/null | cut -f1)
echo "📊 Tamanho final do projeto: $TAMANHO_FINAL"
echo ""

echo "📊 O que foi MANTIDO:"
echo "   ✅ app/ (código fonte Laravel)"
echo "   ✅ resources/ (views e assets)"
echo "   ✅ routes/ (rotas)"
echo "   ✅ config/ (configurações)"
echo "   ✅ database/ (migrations)"
echo "   ✅ public/ (arquivos públicos)"
echo "   ✅ frontend/ (código frontend)"
echo "   ✅ scripts/ (scripts úteis)"
echo "   ✅ vendor/ (dependências PHP)"
echo "   ✅ README.md e documentação essencial"
echo ""

echo "💚 Projeto limpo e pronto para uso! 💸"
