<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Histórico de Envios - ') . $cobranca->descricao }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Envios para a Cobrança</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Erro</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @if($envios->count())
                                @foreach($envios as $envio)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($envio->tipo) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="{{ $envio->status == 'falhou' ? 'text-red-600' : 'text-green-600' }}">
                                            {{ ucfirst($envio->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $envio->data->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $envio->erro ?: '-' }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Nenhum envio registrado.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    <div class="mt-4">
                        <a href="{{ route('painel.index') }}" class="text-indigo-600 hover:text-indigo-900">Voltar ao Painel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>