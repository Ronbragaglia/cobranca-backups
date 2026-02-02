<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Relatórios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Filtros</h3>
                    
                    <form method="GET" action="{{ route('relatorios.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select
                                id="status"
                                name="status"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            >
                                <option value="">Todos</option>
                                <option value="pendente" {{ $status === 'pendente' ? 'selected' : '' }}>Pendente</option>
                                <option value="pago" {{ $status === 'pago' ? 'selected' : '' }}>Pago</option>
                                <option value="cancelado" {{ $status === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>

                        <!-- Data Início -->
                        <div>
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                                Data Início
                            </label>
                            <input
                                type="date"
                                id="data_inicio"
                                name="data_inicio"
                                value="{{ $dataInicio }}"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            >
                        </div>

                        <!-- Data Fim -->
                        <div>
                            <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-1">
                                Data Fim
                            </label>
                            <input
                                type="date"
                                id="data_fim"
                                name="data_fim"
                                value="{{ $dataFim }}"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            >
                        </div>

                        <!-- Botões -->
                        <div class="flex items-end gap-2">
                            <button
                                type="submit"
                                class="flex-1 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                            >
                                Filtrar
                            </button>
                            <a
                                href="{{ route('relatorios.index') }}"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                            >
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cards de Totais -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-600">Total de Cobranças</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $totais['total'] }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-600">Valor Total</p>
                        <p class="text-3xl font-bold text-gray-900">R$ {{ number_format($totais['valor_total'], 2, ',', '.') }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-600">Valor Pago</p>
                        <p class="text-3xl font-bold text-green-600">R$ {{ number_format($totais['valor_pago'], 2, ',', '.') }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-600">Valor Pendente</p>
                        <p class="text-3xl font-bold text-blue-600">R$ {{ number_format($totais['valor_pendente'], 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Resumo por Status -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-600">Ativas</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $totais['ativas'] }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-600">Pagas</p>
                        <p class="text-2xl font-bold text-green-600">{{ $totais['pagas'] }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-600">Vencidas</p>
                        <p class="text-2xl font-bold text-red-600">{{ $totais['vencidas'] }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-600">Canceladas</p>
                        <p class="text-2xl font-bold text-gray-600">{{ $totais['canceladas'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Tabela de Cobranças -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Cobranças</h3>
                        <form method="GET" action="{{ route('relatorios.export') }}" class="inline">
                            @foreach(['status', 'data_inicio', 'data_fim'] as $field)
                                @if(request($field))
                                    <input type="hidden" name="{{ $field }}" value="{{ request($field) }}">
                                @endif
                            @endforeach
                            <button
                                type="submit"
                                class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
                            >
                                Exportar CSV
                            </button>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if($cobrancas->count())
                                    @foreach($cobrancas as $cobranca)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $cobranca->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cobranca->descricao }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">R$ {{ number_format($cobranca->valor, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($cobranca->status == 'pago')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Pago</span>
                                            @elseif($cobranca->status == 'pendente')
                                                @if($cobranca->data_vencimento && $cobranca->data_vencimento < now())
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Vencida</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Pendente</span>
                                                @endif
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($cobranca->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $cobranca->data_vencimento ? $cobranca->data_vencimento->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cobranca->telefone }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $cobranca->created_at->format('d/m/Y H:i') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            Nenhuma cobrança encontrada com os filtros selecionados.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
