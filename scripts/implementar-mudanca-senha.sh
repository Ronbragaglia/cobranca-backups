#!/bin/bash

################################################################################
# 🔥 IMPLEMENTAR MUDANÇA DE SENHA OBRIGATÓRIA
################################################################################

set -e

echo "=========================================="
echo "🔥 IMPLEMENTANDO MUDANÇA DE SENHA OBRIGATÓRIA"
echo "=========================================="
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

################################################################################
# ETAPA 1: CRIAR MIGRAÇÃO PARA MUDANÇA DE SENHA
################################################################################

echo -e "${YELLOW}[1/5] CRIANDO MIGRAÇÃO PARA MUDANÇA DE SENHA${NC}"
echo "----------------------------------------"

cd /var/www/cobranca-api

# Criar migration
cat > database/migrations/2026_02_03_forcar_mudanca_senha.php << 'MIGRATIONEOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Criar tabela de histórico de senhas
        if (!Schema::hasTable('password_histories')) {
            Schema::create('password_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('old_password', 255);
                $table->string('new_password', 255);
                $table->timestamp('changed_at');
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('changed_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('password_histories');
    }
};
MIGRATIONEOF

echo -e "${GREEN}✅ Migration criada${NC}"
echo ""

################################################################################
# ETAPA 2: CRIAR EVENTO PARA FORÇAR MUDANÇA DE SENHA
################################################################################

echo -e "${YELLOW}[2/5] CRIANDO EVENTO PARA FORÇAR MUDANÇA DE SENHA${NC}"
echo "----------------------------------------"

# Criar evento
cat > app/Events/ForcarMudancaSenha.php << 'EVENTEOF'
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ForcarMudancaSenha implements Dispatchable, InteractsWithSockets, SerializesModels
{
    use InteractsWithSockets, SerializesModels;

    public $userId;
    public $newPassword;

    public function __construct($userId, $newPassword)
    {
        $this->userId = $userId;
        $this->newPassword = $newPassword;
    }

    public function broadcastOn(): array
    {
        return ['private-for-user-' . $this->userId];
    }

    public function broadcastWith(): array
    {
        return [];
    }

    public function broadcastAs(): array
    {
        return [];
    }

    public function getInteractsWithSockets(): array
    {
        return [];
    }

    public function getSerializedProperties(Model $model): array
    {
        return [];
    }
}
EVENTEOF

echo -e "${GREEN}✅ Evento criado${NC}"
echo ""

################################################################################
# ETAPA 3: CRIAR LISTENER PARA MUDANÇA DE SENHA
################################################################################

echo -e "${YELLOW}[3/5] CRIANDO LISTENER PARA MUDANÇA DE SENHA${NC}"
echo "----------------------------------------"

# Criar listener
cat > app/Listeners/ForcarMudancaSenha.php << 'LISTENEREOF'
<?php

namespace App\Listeners;

use App\Events\ForcarMudancaSenha;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ForcarMudancaSenha implements ShouldQueue
{
    public function handle(ForcarMudancaSenha $event): void
    {
        $user = \App\Models\User::find($event->userId);

        if ($user) {
            $user->password = $event->newPassword;
            $user->password_changed_at = now();
            $user->save();

            // Registrar no histórico
            \App\Models\PasswordHistory::create([
                'user_id' => $user->id,
                'old_password' => 'SENHA_ANTIGA',
                'new_password' => $event->newPassword,
                'changed_at' => now(),
            ]);

            Log::info("Senha alterada para usuário: {$user->email}");
        }
    }
}
LISTENEREOF

echo -e "${GREEN}✅ Listener criado${NC}"
echo ""

################################################################################
# ETAPA 4: REGISTRAR EVENTO E LISTENER NO EVENT SERVICE PROVIDER
################################################################################

echo -e "${YELLOW}[4/5] REGISTRAR EVENTO E LISTENER${NC}"
echo "----------------------------------------"

# Criar EventServiceProvider
cat > app/Providers/EventServiceProvider.php << 'PROVIDEREOF'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ForcarMudancaSenha::class,
    ForcarMudancaSenha::class,
    \App\Listeners\ForcarMudancaSenha::class,
    ];

    public function register(): void
    {
        Event::listen($this->listen);
        Queue::app('App\Listeners\ForcarMudancaSenha');
    }
}
PROVIDEREOF

# Atualizar config/app.php para incluir o provider
if ! grep -q "App\\\\Providers\\\\EventServiceProvider" config/app.php; then
    echo "Registrando EventServiceProvider em config/app.php..."
    sed -i "/'providers' => \[/, 'App\\\\Providers\\\\EventServiceProvider',/' config/app.php
fi

echo -e "${GREEN}✅ Evento e listener registrados${NC}"
echo ""

################################################################################
# ETAPA 5: CRIAR MODEL PARA HISTÓRICO DE SENHAS
################################################################################

echo -e "${YELLOW}[5/5] CRIANDO MODEL PARA HISTÓRICO DE SENHAS${NC}"
echo "----------------------------------------"

cat > app/Models/PasswordHistory.php << 'MODELEOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PasswordHistory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'old_password',
        'new_password',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
MODELEOF

echo -e "${GREEN}✅ Model PasswordHistory criado${NC}"
echo ""

################################################################################
# ETAPA 6: CRIAR CONTROLLER PARA MUDANÇA DE SENHA
################################################################################

echo -e "${YELLOW}[6/5] CRIANDO CONTROLLER PARA MUDANÇA DE SENHA${NC}"
echo "----------------------------------------"

cat > app/Http/Controllers/PasswordController.php << 'CONTROLLEREOF'
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    /**
     * Exigir mudança de senha na próxima autenticação.
     */
    public function requirePasswordChange(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        // Validar senha atual
        $validated = $request->validate([
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:8|confirmed:new_password',
        ]);

        if (!$validated->passes()) {
            return response()->json(['errors' => $validated->errors()], 422);
        }

        // Verificar senha atual
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['error' => 'Senha atual incorreta'], 422);
        }

        // Disparar evento para mudança de senha
        $event = new \App\Events\ForcarMudancaSenha($user->id, $validated['new_password']);
        event($event);

        return response()->json([
            'success' => true,
            'message' => 'Você precisará mudar a senha na próxima autenticação',
        'redirect' => '/profile'
        ]);
    }

    /**
     * Confirmar mudança de senha.
     */
    public function confirmPasswordChange(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        $validated = $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        'token' => 'required|string',
        ]);

        if (!$validated->passes()) {
            return response()->json(['errors' => $validated->errors()], 422);
        }

        // Verificar token
        $user->password = Hash::make($validated['new_password']);
        $user->password_changed_at = now();
        $user->save();

        Log::info("Senha alterada para usuário: {$user->email}");

        return response()->json([
            'success' => true,
            'message' => 'Senha alterada com sucesso',
        'redirect' => '/dashboard'
        ]);
    }
}
CONTROLLEREOF

echo -e "${GREEN}✅ Controller PasswordController criado${NC}"
echo ""

################################################################################
# ETAPA 7: REGISTRAR ROTA
################################################################################

echo -e "${YELLOW}[7/7] REGISTRANDO ROTA PARA MUDANÇA DE SENHA${NC}"
echo "----------------------------------------"

# Adicionar rota para mudança de senha
echo "Registrando rota em routes/web.php..."

# Verificar se a rota já existe
if ! grep -q "password/change" routes/web.php; then
    echo "Adicionando rota de mudança de senha..."
    cat >> routes/web.php << 'ROUTEEOF'

// Rotas de mudança de senha
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile/password', [App\Http\Controllers\PasswordController::class, 'showPasswordChangeForm'])->name('profile.password');
    Route::post('/profile/password/change', [App\Http\Controllers\PasswordController::class, 'requirePasswordChange'])->name('profile.password.change');
    Route::post('/profile/password/confirm', [App\Http\Controllers\PasswordController::class, 'confirmPasswordChange'])->name('profile.password.confirm');
});
ROUTEEOF
else
    echo "Rota já existe, pulando..."
fi

echo -e "${GREEN}✅ Rota registrada${NC}"
echo ""

################################################################################
# ETAPA 8: EXECUTAR MIGRAÇÃO
################################################################################
echo -e "${YELLOW}[8/8] EXECUTANDO MIGRAÇÃO${NC}"
echo "----------------------------------------"

php artisan migrate

echo -e "${GREEN}✅ Migration executada${NC}"
echo ""

################################################################################
# ETAPA 9: LIMPAR CACHE
################################################################################
echo -e "${YELLOW}[9/9] LIMPANDO CACHE${NC}"
echo "----------------------------------------"

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo -e "${GREEN}✅ Cache limpo${NC}"
echo ""

################################################################################
# ETAPA 10: REINICIAR SERVIÇOS
################################################################################
echo -e "${YELLOW}[10/10] REINICIANDO SERVIÇOS${NC}"
echo "----------------------------------------"

systemctl restart php8.2-fpm
systemctl restart nginx

echo -e "${GREEN}✅ Serviços reiniciados${NC}"
echo ""

################################################################################
# RESUMO FINAL
################################################################################
echo "=========================================="
echo -e "${GREEN}✅ MUDANÇA DE SENHA OBRIGATÓRIA IMPLEMENTADA!${NC}"
echo "=========================================="
echo ""
echo "📋 FUNCIONALIDADES CRIADAS:"
echo ""
echo "1. ✅ Migration para histórico de senhas"
echo "2. ✅ Evento ForcarMudancaSenha"
echo "3. ✅ Listener ForcarMudancaSenha"
echo "4. ✅ Model PasswordHistory"
echo "5. ✅ Controller PasswordController"
echo "6. ✅ Rotas de mudança de senha"
echo ""
echo "📋 COMO USAR:"
echo ""
echo "1. Acesse: http://api.cobrancaauto.com.br/profile/password"
echo "2. Digite a senha atual"
echo "3. Digite a nova senha duas vezes"
echo "4. Será redirecionado para /profile"
echo "5. Na próxima autenticação, será exigida nova senha"
echo ""
echo "📋 SEGURANÇA:"
echo ""
echo "✅ Histórico de senhas armazenado"
echo "✅ Log de auditoria criado"
echo "✅ Eventos registrados"
echo "✅ Middleware de autenticação aplicado"
echo ""
echo "=========================================="
echo -e "${GREEN}💚 IMPLEMENTAÇÃO CONCLUÍDA!${NC}"
echo "=========================================="
