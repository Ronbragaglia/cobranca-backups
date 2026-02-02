<?php

namespace App\Console\Commands;

use App\Jobs\SendWhatsAppNotification;
use App\Models\Cobranca;
use Illuminate\Console\Command;

class SendWhatsAppOverdue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:send-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar notificações WhatsApp para cobranças vencidas há mais de 3 dias';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $overdueCobrancas = Cobranca::where('status', 'pendente')
            ->where('data_vencimento', '<', now()->subDays(3))
            ->whereNotNull('telefone')
            ->get();

        foreach ($overdueCobrancas as $cobranca) {
            SendWhatsAppNotification::dispatch($cobranca);
        }

        $this->info("Enviados {$overdueCobrancas->count()} jobs de WhatsApp para cobranças vencidas.");
    }
}