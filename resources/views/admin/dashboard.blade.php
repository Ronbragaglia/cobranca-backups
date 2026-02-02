<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cobrança Auto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-gray-800 shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-green-400">📊 Dashboard Admin</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-400">Atualizado: {{ now()->format('d/m/Y H:i') }}</span>
                        <a href="{{ route('admin.metrics') }}" class="text-green-400 hover:text-green-300">Métricas</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total de Cobranças -->
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 00-2-2h2a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-400">Total de Cobranças</p>
                            <p class="text-3xl font-bold">{{ number_format($totalCobrancas) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cobranças Pagas -->
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a2 2 0 002-2h-8a2 2 0 00-2 2v6a2 2 0 002 2h8a2 2 0 002-2v-6a2 2 0 00-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-400">Cobranças Pagas</p>
                            <p class="text-3xl font-bold text-green-400">{{ number_format($cobrancasPagas) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cobranças Pendentes -->
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-500 bg-opacity-20">
                            <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3v-4l3 3M6 21l12-12M12 21V3"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-400">Cobranças Pendentes</p>
                            <p class="text-3xl font-bold text-yellow-400">{{ number_format($cobrancasPendentes) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cobranças Atrasadas -->
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-500 bg-opacity-20">
                            <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 0v-2m0 0v2m0 0v-2m0 0v2m-2 12h14a2 2 0 002-2h-1m-1 0V7a2 2 0 012-2h-3m-3 0V5a2 2 0 012-2h-3m-3 0V4a2 2 0 012-2h-3m-3 0V3a2 2 0 012-2h-3m-3 0V2a2 2 0 012-2h-3m-3 0V1a2 2 0 012-2h-3"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-400">Cobranças Atrasadas</p>
                            <p class="text-3xl font-bold text-red-400">{{ number_format($cobrancasAtrasadas) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valores -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <p class="text-sm text-gray-400 mb-2">Valor Total</p>
                    <p class="text-2xl font-bold">R$ {{ number_format($valorTotal, 2, ',', '.') }}</p>
                </div>
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <p class="text-sm text-gray-400 mb-2">Valor Pago</p>
                    <p class="text-2xl font-bold text-green-400">R$ {{ number_format($valorPago, 2, ',', '.') }}</p>
                </div>
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <p class="text-sm text-gray-400 mb-2">Valor Pendente</p>
                    <p class="text-2xl font-bold text-yellow-400">R$ {{ number_format($valorPendente, 2, ',', '.') }}</p>
                </div>
            </div>

            <!-- Métricas de WhatsApp -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <p class="text-sm text-gray-400 mb-2">Mensagens Enviadas (7 dias)</p>
                    <p class="text-2xl font-bold text-blue-400">{{ number_format($mensagensEnviadas) }}</p>
                </div>
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <p class="text-sm text-gray-400 mb-2">Mensagens com Sucesso</p>
                    <p class="text-2xl font-bold text-green-400">{{ number_format($mensagensSucesso) }}</p>
                </div>
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <p class="text-sm text-gray-400 mb-2">Taxa de Entrega</p>
                    <p class="text-2xl font-bold">{{ $taxaEntrega }}%</p>
                </div>
            </div>

            <!-- Status Evolution API -->
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
                <h2 class="text-xl font-bold mb-4">📱 Status Evolution API</h2>
                <div class="flex items-center space-x-4">
                    @if($evolutionStatus['connected'])
                        <span class="px-4 py-2 rounded-full bg-green-500 text-white font-bold">✅ ONLINE</span>
                    @else
                        <span class="px-4 py-2 rounded-full bg-red-500 text-white font-bold">❌ OFFLINE</span>
                    @endif
                    <span class="text-gray-400">Status: {{ strtoupper($evolutionStatus['status']) }}</span>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Gráfico de Cobranças por Dia -->
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-bold mb-4">📈 Cobranças (Últimos 7 dias)</h3>
                    <canvas id="cobrancasChart" height="200"></canvas>
                </div>

                <!-- Gráfico de Status -->
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-bold mb-4">📊 Status das Cobranças</h3>
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>

            <!-- Cobranças Vencendo Hoje -->
            @if($vencendoHoje->count() > 0)
                <div class="bg-yellow-900 bg-opacity-20 border border-yellow-500 rounded-lg shadow-lg p-6 mb-8">
                    <h3 class="text-lg font-bold mb-4 text-yellow-400">⚠️ Cobranças Vencendo Hoje ({{ $vencendoHoje->count() }})</h3>
                    <div class="space-y-3">
                        @foreach($vencendoHoje as $cobranca)
                            <div class="flex justify-between items-center bg-gray-800 rounded p-3">
                                <div>
                                    <p class="font-bold">{{ $cobranca->nome }}</p>
                                    <p class="text-sm text-gray-400">{{ $cobranca->telefone }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-yellow-400">R$ {{ number_format($cobranca->valor, 2, ',', '.') }}</p>
                                    <p class="text-sm text-gray-400">{{ $cobranca->data_vencimento->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Últimas Cobranças -->
            <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold mb-4">📋 Últimas Cobranças</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Telefone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Valor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Vencimento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($ultimasCobrancas as $cobranca)
                                <tr class="hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $cobranca->nome }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $cobranca->telefone }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">R$ {{ number_format($cobranca->valor, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $cobranca->data_vencimento->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($cobranca->status === 'paga')
                                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-500">PAGA</span>
                                        @elseif($cobranca->status === 'pendente')
                                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-yellow-500">PENDENTE</span>
                                        @elseif($cobranca->status === 'atrasada')
                                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-500">ATRASADA</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-gray-500">{{ strtoupper($cobranca->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Gráfico de Cobranças por Dia
        const cobrancasCtx = document.getElementById('cobrancasChart').getContext('2d');
        new Chart(cobrancasCtx, {
            type: 'line',
            data: {
                labels: @json($cobrancasPorDia->pluck('data')),
                datasets: [{
                    label: 'Total',
                    data: @json($cobrancasPorDia->pluck('total')),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1
                }, {
                    label: 'Pagas',
                    data: @json($cobrancasPorDia->pluck('pagas')),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgb(156, 163, 175)'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: 'rgb(156, 163, 175)' },
                        grid: { color: 'rgb(75, 85, 99)' }
                    },
                    y: {
                        ticks: { color: 'rgb(156, 163, 175)' },
                        grid: { color: 'rgb(75, 85, 99)' }
                    }
                }
            }
        });

        // Gráfico de Status
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paga', 'Pendente', 'Atrasada', 'Cancelada'],
                datasets: [{
                    data: [
                        {{ $cobrancasPorStatus['paga'] }},
                        {{ $cobrancasPorStatus['pendente'] }},
                        {{ $cobrancasPorStatus['atrasada'] }},
                        {{ $cobrancasPorStatus['cancelada'] }}
                    ],
                    backgroundColor: [
                        'rgb(75, 192, 192)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(107, 114, 128)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgb(156, 163, 175)'
                        }
                    }
                }
            }
        });

        // Atualizar métricas em tempo real (a cada 30 segundos)
        setInterval(() => {
            fetch('/admin/metrics')
                .then(response => response.json())
                .then(data => {
                    document.querySelector('[data-metric="mensagens_enviadas_hoje"]').textContent = data.mensagens_enviadas_hoje;
                    document.querySelector('[data-metric="taxa_entrega"]').textContent = data.taxa_entrega + '%';
                });
        }, 30000);
    </script>
</body>
</html>
