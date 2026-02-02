<?php

namespace App\Console\Commands;

use App\Jobs\SendWhatsAppReminder;
use App\Models\Cobranca;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar lembretes WhatsApp para cobranças D-3, D-1 e D+1';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $sentCount = 0;

        // D-3: vencimento em 3 dias
        $d3Cobrancas = Cobranca::where('status', 'pendente')
            ->where('data_vencimento', $today->copy()->addDays(3)->toDateString())
            ->whereNotNull('telefone')
            ->where(function ($query) {
                $query->whereNull('whatsapp_reminders_sent')
                      ->orWhereRaw("NOT ('d-3' = ANY(whatsapp_reminders_sent))");
            })
            ->get();

        foreach ($d3Cobrancas as $cobranca) {
            SendWhatsAppReminder::dispatch($cobranca, 'd-3');
            $sentCount++;
        }

        // D-1: vencimento amanhã
        $d1Cobrancas = Cobranca::where('status', 'pendente')
            ->where('data_vencimento', $today->copy()->addDay()->toDateString())
            ->whereNotNull('telefone')
            ->where(function ($query) {
                $query->whereNull('whatsapp_reminders_sent')
                      ->orWhereRaw("NOT ('d-1' = ANY(whatsapp_reminders_sent))");
            })
            ->get();

        foreach ($d1Cobrancas as $cobranca) {
            SendWhatsAppReminder::dispatch($cobranca, 'd-1');
            $sentCount++;
        }

        // D+1: vencida ontem
        $dp1Cobrancas = Cobranca::where('status', 'pendente')
            ->where('data_vencimento', $today->copy()->subDay()->toDateString())
            ->whereNotNull('telefone')
            ->where(function ($query) {
                $query->whereNull('whatsapp_reminders_sent')
                      ->orWhereRaw("NOT ('d+1' = ANY(whatsapp_reminders_sent))");
            })
            ->get();

        foreach ($dp1Cobrancas as $cobranca) {
            SendWhatsAppReminder::dispatch($cobranca, 'd+1');
            $sentCount++;
        }

        $this->info("Enviados {$sentCount} jobs de lembretes WhatsApp.");
    }
}