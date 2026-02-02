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

class SendVencimentosEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Buscar cobranças que vencem hoje
        $vencimentosHoje = Cobranca::where('status', 'pendente')
            ->whereDate('data_vencimento', today())
            ->get();

        if ($vencimentosHoje->isEmpty()) {
            return;
        }

        // Buscar usuários (assumindo que há apenas um admin ou todos os usuários)
        $users = User::all();

        foreach ($users as $user) {
            Mail::raw(
                "Olá {$user->name},\n\nVocê tem {$vencimentosHoje->count()} cobranças vencendo hoje:\n\n" .
                $vencimentosHoje->map(function ($cobranca) {
                    return "- {$cobranca->descricao}: R$ " . number_format($cobranca->valor, 2, ',', '.');
                })->join("\n") .
                "\n\nAcesse o painel para mais detalhes.",
                function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Cobranças Vencendo Hoje');
                }
            );
        }

        // Log envio para cada cobrança
        foreach ($vencimentosHoje as $cobranca) {
            CobrancaEnvio::create([
                'cobranca_id' => $cobranca->id,
                'tipo' => 'email',
                'status' => 'enviado',
                'data' => now(),
            ]);
        }
    }
}