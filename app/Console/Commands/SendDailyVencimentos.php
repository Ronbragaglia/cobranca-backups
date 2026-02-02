<?php

namespace App\Console\Commands;

use App\Jobs\SendVencimentosEmail;
use Illuminate\Console\Command;

class SendDailyVencimentos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vencimentos:send-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar email diário com cobranças vencendo hoje';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SendVencimentosEmail::dispatch();

        $this->info('Job de envio de emails de vencimentos foi agendado.');
    }
}