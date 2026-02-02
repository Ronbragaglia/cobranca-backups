<?php

namespace App\Jobs;

use App\Models\Cobranca;
use App\Models\CobrancaEnvio;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $days;

    /**
     * Create a new job instance.
     */
    public function __construct($days)
    {
        $this->days = $days;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $date = now()->addDays($this->days)->toDateString();

        $cobrancas = Cobranca::where('status', 'pendente')
            ->whereDate('data_vencimento', $date)
            ->get();

        if ($cobrancas->isEmpty()) {
            return;
        }

        $users = User::all();

        $message = $this->getMessage($this->days, $cobrancas);

        foreach ($users as $user) {
            Mail::raw($message, function ($mail) use ($user) {
                $mail->to($user->email)
                     ->subject('Lembrete de Vencimentos');
            });
        }

        // Log envio para cada cobrança
        foreach ($cobrancas as $cobranca) {
            CobrancaEnvio::create([
                'cobranca_id' => $cobranca->id,
                'tipo' => 'email',
                'status' => 'enviado',
                'data' => now(),
            ]);
        }
    }

    private function getMessage($days, $cobrancas)
    {
        $period = match ($days) {
            -3 => 'em 3 dias',
            -1 => 'amanhã',
            1 => 'ontem (atrasada)',
            default => "em {$days} dias",
        };

        return "Olá,\n\nVocê tem {$cobrancas->count()} cobranças vencendo {$period}:\n\n" .
               $cobrancas->map(function ($cobranca) {
                   return "- {$cobranca->descricao}: R$ " . number_format($cobranca->valor, 2, ',', '.') . " (Vence em: {$cobranca->data_vencimento->format('d/m/Y')})";
               })->join("\n") .
               "\n\nAcesse o painel para mais detalhes.";
    }
}