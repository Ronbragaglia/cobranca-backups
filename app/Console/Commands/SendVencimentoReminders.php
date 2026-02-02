<?php

namespace App\Console\Commands;

use App\Jobs\SendReminderEmail;
use Illuminate\Console\Command;

class SendVencimentoReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vencimentos:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar lembretes de vencimento para D-3, D-1 e D+1';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Lembretes para D-3
        SendReminderEmail::dispatch(-3);

        // Lembretes para D-1
        SendReminderEmail::dispatch(-1);

        // Lembretes para D+1 (atrasadas)
        SendReminderEmail::dispatch(1);

        $this->info('Jobs de lembretes de vencimento foram agendados.');
    }
}