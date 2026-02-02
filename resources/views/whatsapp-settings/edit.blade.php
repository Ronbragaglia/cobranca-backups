<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Configurações de Lembretes de WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-6">Configure quando enviar lembretes de WhatsApp</h3>

                    <form method="POST" action="{{ route('whatsapp-settings.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Habilitar/Desabilitar Lembretes -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900">Lembretes de WhatsApp</h4>
                                    <p class="text-sm text-gray-500">Envie lembretes automáticos de cobrança via WhatsApp</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input
                                        type="checkbox"
                                        id="lembretes_whatsapp"
                                        name="lembretes_whatsapp"
                                        value="1"
                                        {{ $settings['whatsapp_enabled'] ?? true ? 'checked' : '' }}
                                        class="peer sr-only"
                                    >
                                    <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:ring-4 peer-focus:ring-blue-300"></div>
                                </label>
                            </div>
                        </div>

                        <div id="whatsapp-config" class="space-y-6 {{ !($settings['whatsapp_enabled'] ?? true) ? 'hidden' : '' }}">
                            <!-- Dias Antes do Vencimento -->
                            <div class="rounded-md bg-gray-50 p-4">
                                <h4 class="text-md font-semibold mb-4 text-gray-900">Antes do Vencimento</h4>
                                <p class="text-sm text-gray-600 mb-4">Selecione quantos dias antes do vencimento deseja enviar lembretes:</p>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @foreach([7, 5, 3, 2, 1] as $days)
                                        <label class="inline-flex items-center">
                                            <input 
                                                type="checkbox" 
                                                name="lembretes_dias_antes[]" 
                                                value="{{ $days }}"
                                                {{ in_array($days, $settings['whatsapp_reminder_days_before'] ?? [3, 1]) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            >
                                            <span class="ml-2 text-sm text-gray-700">{{ $days }} dia{{ $days > 1 ? 's' : '' }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Dia do Vencimento -->
                            <div class="rounded-md bg-gray-50 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-md font-semibold text-gray-900">Dia do Vencimento</h4>
                                        <p class="text-sm text-gray-600">Enviar lembrete no dia do vencimento</p>
                                    </div>
                                    <label class="inline-flex items-center">
                                        <input
                                            type="checkbox"
                                            name="lembrete_dia_vencimento"
                                            value="1"
                                            {{ $settings['whatsapp_reminder_on_due_date'] ?? true ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        >
                                    </label>
                                </div>
                            </div>

                            <!-- Dias Após o Vencimento -->
                            <div class="rounded-md bg-gray-50 p-4">
                                <h4 class="text-md font-semibold mb-4 text-gray-900">Após o Vencimento</h4>
                                <p class="text-sm text-gray-600 mb-4">Selecione quantos dias após o vencimento deseja enviar lembretes:</p>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @foreach([1, 2, 3, 5, 7, 10, 15, 30] as $days)
                                        <label class="inline-flex items-center">
                                            <input 
                                                type="checkbox" 
                                                name="lembretes_dias_depois[]" 
                                                value="{{ $days }}"
                                                {{ in_array($days, $settings['whatsapp_reminder_days_after'] ?? [1, 3, 7]) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            >
                                            <span class="ml-2 text-sm text-gray-700">{{ $days }} dia{{ $days > 1 ? 's' : '' }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="mt-8 flex gap-3">
                            <a
                                href="{{ route('painel.index') }}"
                                class="flex-1 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600 text-center"
                            >
                                Cancelar
                            </a>
                            <button
                                type="submit"
                                class="flex-1 rounded-md bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                            >
                                Salvar Configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.getElementById('lembretes_whatsapp').addEventListener('change', function() {
    const config = document.getElementById('whatsapp-config');
    config.classList.toggle('hidden', !this.checked);
});
</script>
