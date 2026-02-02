<?php

namespace App\Console\Commands;

use App\Services\SecurityAlertService;
use Illuminate\Console\Command;

class CheckSecurityAlerts extends Command
{
    protected $signature = 'security:check-alerts';
    
    protected $description = 'Verifica alertas de segurança e envia notificações';

    public function handle(): int
    {
        $this->info('🔒 Verificando alertas de segurança...');

        SecurityAlertService::checkAndAlert();

        $this->info('✅ Verificação de segurança concluída!');

        return Command::SUCCESS;
    }
}
