@extends('layouts.guest')

@section('title', 'Onboarding - Passo 3')

@section('content')
<div class="flex min-h-screen items-center justify-center bg-gray-100 px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-2xl">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900">CobrançaAuto</h1>
            <p class="mt-2 text-sm text-gray-600">Sistema de Cobranças Automatizadas</p>
        </div>

        <!-- Progresso -->
        <div class="mb-8">
            <div class="flex justify-between text-sm">
                <span class="font-semibold text-blue-600">Passo 3 de 3</span>
                <span class="text-gray-500">Preferências de Cobrança</span>
            </div>
            <div class="mt-2 h-2 rounded-full bg-gray-200">
                <div class="h-2 rounded-full bg-blue-600" style="width: 100%"></div>
            </div>
        </div>

        <div class="bg-white px-4 py-8 shadow sm:rounded-lg sm:px-10">
            <h2 class="mb-6 text-2xl font-bold text-gray-900">Configure suas preferências</h2>

            <form method="POST" action="{{ route('onboarding.step3.store') }}">
                @csrf

                <!-- Configurações Básicas -->
                <div class="mb-6 rounded-md bg-gray-50 p-4">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Configurações Básicas</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="moeda" class="block text-sm font-medium text-gray-700">
                                Moeda
                            </label>
                            <select
                                id="moeda"
                                name="moeda"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                required
                            >
                                <option value="BRL" selected>Real (BRL)</option>
                                <option value="USD">Dólar (USD)</option>
                                <option value="EUR">Euro (EUR)</option>
                            </select>
                        </div>

                        <div>
                            <label for="dias_vencimento" class="block text-sm font-medium text-gray-700">
                                Dias Padrão de Vencimento
                            </label>
                            <input
                                id="dias_vencimento"
                                type="number"
                                name="dias_vencimento"
                                value="7"
                                min="1"
                                max="90"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                required
                            >
                        </div>
                    </div>
                </div>

                <!-- Lembretes de WhatsApp -->
                <div class="mb-6 rounded-md bg-gray-50 p-4">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Lembretes de WhatsApp</h3>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input
                                type="checkbox"
                                id="lembretes_whatsapp"
                                name="lembretes_whatsapp"
                                value="1"
                                checked
                                class="peer sr-only"
                            >
                            <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:ring-4 peer-focus:ring-blue-300"></div>
                        </label>
                    </div>

                    <div id="whatsapp-config" class="space-y-4">
                        <!-- Dias Antes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Enviar lembretes antes do vencimento (dias)
                            </label>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="lembretes_dias_antes[]" value="7" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">7 dias</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="lembretes_dias_antes[]" value="3" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">3 dias</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="lembretes_dias_antes[]" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">1 dia</span>
                                </label>
                            </div>
                        </div>

                        <!-- Dia do Vencimento -->
                        <div>
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="lembrete_dia_vencimento"
                                    name="lembrete_dia_vencimento"
                                    value="1"
                                    checked
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Enviar lembrete no dia do vencimento</span>
                            </label>
                        </div>

                        <!-- Dias Depois -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Enviar lembretes após o vencimento (dias)
                            </label>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="lembretes_dias_depois[]" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">1 dia</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="lembretes_dias_depois[]" value="3" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">3 dias</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="lembretes_dias_depois[]" value="7" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">7 dias</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6 flex gap-3">
                    <a
                        href="{{ route('onboarding.step2') }}"
                        class="flex w-1/3 justify-center rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                    >
                        Voltar
                    </a>
                    <button
                        type="submit"
                        class="flex w-2/3 justify-center rounded-md bg-green-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
                    >
                        Finalizar e Começar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('lembretes_whatsapp').addEventListener('change', function() {
    const config = document.getElementById('whatsapp-config');
    config.style.display = this.checked ? 'block' : 'none';
});
</script>
@endsection
