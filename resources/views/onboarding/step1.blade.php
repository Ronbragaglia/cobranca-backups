@extends('layouts.guest')

@section('title', 'Onboarding - Passo 1')

@section('content')
<div class="flex min-h-screen items-center justify-center bg-gray-100 px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900">CobrançaAuto</h1>
            <p class="mt-2 text-sm text-gray-600">Sistema de Cobranças Automatizadas</p>
        </div>

        <!-- Progresso -->
        <div class="mb-8">
            <div class="flex justify-between text-sm">
                <span class="font-semibold text-blue-600">Passo 1 de 3</span>
                <span class="text-gray-500">Dados da Empresa</span>
            </div>
            <div class="mt-2 h-2 rounded-full bg-gray-200">
                <div class="h-2 rounded-full bg-blue-600" style="width: 33%"></div>
            </div>
        </div>

        <div class="bg-white px-4 py-8 shadow sm:rounded-lg sm:px-10">
            <h2 class="mb-6 text-2xl font-bold text-gray-900">Configure sua empresa</h2>

            <form method="POST" action="{{ route('onboarding.step1.store') }}">
                @csrf

                <!-- Nome da Empresa -->
                <div class="mb-4">
                    <label for="empresa_nome" class="block text-sm font-medium text-gray-700">
                        Nome da Empresa
                    </label>
                    <input
                        id="empresa_nome"
                        type="text"
                        name="empresa_nome"
                        value="{{ old('empresa_nome') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        required
                        autofocus
                    >
                    @error('empresa_nome')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Subdomínio -->
                <div class="mb-6">
                    <label for="empresa_subdominio" class="block text-sm font-medium text-gray-700">
                        Subdomínio
                    </label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-gray-500 sm:text-sm">
                            https://
                        </span>
                        <input
                            id="empresa_subdominio"
                            type="text"
                            name="empresa_subdominio"
                            value="{{ old('empresa_subdominio') }}"
                            class="block w-full rounded-none rounded-r-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            required
                            placeholder="minha-empresa"
                        >
                        <span class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-gray-50 px-3 text-gray-500 sm:text-sm">
                            .cobrancaauto.com
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Use apenas letras minúsculas, números e hífens. Ex: minha-empresa
                    </p>
                    @error('empresa_subdominio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="flex w-full justify-center rounded-md bg-blue-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                >
                    Continuar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
