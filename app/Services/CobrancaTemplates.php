<?php

namespace App\Services;

class CobrancaTemplates
{
    /**
     * Template de cobrança padrão
     */
    public static function cobrancaPadrao($nome, $valor, $dataVencimento, $linkPagamento = null)
    {
        $mensagem = "💰 *COBRANCA*\n\n";
        $mensagem .= "Olá, *{$nome}*!\n\n";
        $mensagem .= "Valor: *R$ {$valor}*\n";
        $mensagem .= "Vencimento: *{$dataVencimento}*\n\n";

        if ($linkPagamento) {
            $mensagem .= "🔗 Link para pagamento:\n{$linkPagamento}\n\n";
        }

        $mensagem .= "Por favor, efetue o pagamento até a data de vencimento.";

        return $mensagem;
    }

    /**
     * Template de lembrete de cobrança
     */
    public static function lembreteCobranca($nome, $valor, $dataVencimento, $diasAtraso = 0)
    {
        $mensagem = "⏰ *LEMBRETE DE COBRANCA*\n\n";
        $mensagem .= "Olá, *{$nome}*!\n\n";

        if ($diasAtraso > 0) {
            $mensagem .= "Você tem uma cobrança em atraso:\n\n";
            $mensagem .= "Valor: *R$ {$valor}*\n";
            $mensagem .= "Vencimento: *{$dataVencimento}*\n";
            $mensagem .= "Dias de atraso: *{$diasAtraso} dias*\n\n";
            $mensagem .= "Por favor, efetue o pagamento o mais breve possível para evitar juros.";
        } else {
            $mensagem .= "Lembrete de cobrança:\n\n";
            $mensagem .= "Valor: *R$ {$valor}*\n";
            $mensagem .= "Vencimento: *{$dataVencimento}*\n\n";
            $mensagem .= "Por favor, efetue o pagamento até a data de vencimento.";
        }

        return $mensagem;
    }

    /**
     * Template de confirmação de pagamento
     */
    public static function confirmacaoPagamento($nome, $valor, $dataPagamento)
    {
        $mensagem = "✅ *PAGAMENTO CONFIRMADO*\n\n";
        $mensagem .= "Olá, *{$nome}*!\n\n";
        $mensagem .= "Recebemos seu pagamento de *R$ {$valor}*\n";
        $mensagem .= "Data: *{$dataPagamento}*\n\n";
        $mensagem .= "Obrigado pela preferência!";

        return $mensagem;
    }

    /**
     * Template de cobrança vencendo hoje
     */
    public static function cobrancaVencendoHoje($nome, $valor)
    {
        $mensagem = "⚠️ *COBRANÇA VENCENDO HOJE*\n\n";
        $mensagem .= "Olá, *{$nome}*!\n\n";
        $mensagem .= "Você tem uma cobrança que vence hoje:\n\n";
        $mensagem .= "Valor: *R$ {$valor}*\n\n";
        $mensagem .= "Por favor, efetue o pagamento hoje para evitar problemas.";

        return $mensagem;
    }

    /**
     * Template de cobrança com desconto
     */
    public static function cobrancaComDesconto($nome, $valor, $dataVencimento, $desconto, $linkPagamento = null)
    {
        $mensagem = "🎉 *COBRANCA COM DESCONTO*\n\n";
        $mensagem .= "Olá, *{$nome}*!\n\n";
        $mensagem .= "Valor original: R$ {$valor}\n";
        $mensagem .= "Desconto: {$desconto}%\n";
        $mensagem .= "Valor com desconto: *R$ {$valor}*\n";
        $mensagem .= "Vencimento: *{$dataVencimento}*\n\n";

        if ($linkPagamento) {
            $mensagem .= "🔗 Link para pagamento:\n{$linkPagamento}\n\n";
        }

        $mensagem .= "Aproveite o desconto e efetue o pagamento até a data de vencimento.";

        return $mensagem;
    }

    /**
     * Template de cobrança parcelada
     */
    public static function cobrancaParcelada($nome, $valorTotal, $numeroParcelas, $dataVencimento, $linkPagamento = null)
    {
        $valorParcela = $valorTotal / $numeroParcelas;

        $mensagem = "📦 *COBRANCA PARCELADA*\n\n";
        $mensagem .= "Olá, *{$nome}*!\n\n";
        $mensagem .= "Valor total: *R$ {$valorTotal}*\n";
        $mensagem .= "Número de parcelas: *{$numeroParcelas}*\n";
        $mensagem .= "Valor da parcela: *R$ {$valorParcela}*\n";
        $mensagem .= "Vencimento: *{$dataVencimento}*\n\n";

        if ($linkPagamento) {
            $mensagem .= "🔗 Link para pagamento:\n{$linkPagamento}\n\n";
        }

        $mensagem .= "Por favor, efetue o pagamento até a data de vencimento.";

        return $mensagem;
    }

    /**
     * Template de aviso de bloqueio
     */
    public static function avisoBloqueio($nome, $valor, $diasAtraso)
    {
        $mensagem = "🚫 *AVISO DE BLOQUEIO*\n\n";
        $mensagem .= "Olá, *{$nome}*!\n\n";
        $mensagem .= "Sua cobrança está em atraso há *{$diasAtraso} dias*\n";
        $mensagem .= "Valor: *R$ {$valor}*\n\n";
        $mensagem .= "Por favor, entre em contato conosco para regularizar sua situação.\n\n";
        $mensagem .= "Caso contrário, seu serviço poderá ser suspenso.";

        return $mensagem;
    }

    /**
     * Template de boas vindas
     */
    public static function boasVindas($nome)
    {
        $mensagem = "👋 *BOAS VINDAS*\n\n";
        $mensagem .= "Olá, *{$nome}*!\n\n";
        $mensagem .= "Bem-vindo ao nosso sistema de cobranças!\n\n";
        $mensagem .= "Agora você pode receber suas cobranças e lembretes diretamente no WhatsApp.\n\n";
        $mensagem .= "Para mais informações, entre em contato com nosso suporte.";

        return $mensagem;
    }

    /**
     * Template de atualização de dados
     */
    public static function atualizacaoDados($nome, $campo)
    {
        $mensagem = "📝 *ATUALIZAÇÃO DE DADOS*\n\n";
        $mensagem .= "Olá, *{$nome}*!\n\n";
        $mensagem .= "Seus dados foram atualizados:\n";
        $mensagem .= "Campo alterado: *{$campo}*\n\n";
        $mensagem .= "Caso não tenha sido você, entre em contato conosco.";

        return $mensagem;
    }

    /**
     * Template de promoção
     */
    public static function promocao($nome, $descricao, $linkPromocao = null)
    {
        $mensagem = "🎁 *PROMOÇÃO ESPECIAL*\n\n";
        $mensagem .= "Olá, *{$nome}*!\n\n";
        $mensagem .= "Temos uma promoção especial para você:\n\n";
        $mensagem .= "{$descricao}\n\n";

        if ($linkPromocao) {
            $mensagem .= "🔗 Aproveite agora:\n{$linkPromocao}";
        }

        return $mensagem;
    }
}
