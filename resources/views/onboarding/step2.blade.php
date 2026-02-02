@extends('layouts.guest')

@section('title', 'Onboarding - Passo 2')

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
                <span class="font-semibold text-blue-600">Passo 2 de 3</span>
                <span class="text-gray-500">Dados do Responsável</span>
            </div>
            <div class="mt-2 h-2 rounded-full bg-gray-200">
                <div class="h-2 rounded-full bg-blue-600" style="width: 66%"></div>
            </div>
        </div>

        <div class="bg-white px-4 py-8 shadow sm:rounded-lg sm:px-10">
            <h2 class="mb-6 text-2xl font-bold text-gray-900">Crie sua conta</h2>

            <form method="POST" action="{{ route('onboarding.step2.store') }}">
                @csrf

                <!-- Nome -->
                <div class="mb-4">
                    <label for="nome" class="block text-sm font-medium text-gray-700">
                        Nome Completo
                    </label>
                    <input
                        id="nome"
                        type="text"
                        name="nome"
                        value="{{ old('nome') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        required
                        autofocus
                    >
                    @error('nome')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        E-mail
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        required
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telefone -->
                <div class="mb-4">
                    <label for="telefone" class="block text-sm font-medium text-gray-700">
                        Telefone (WhatsApp)
                    </label>
                    <input
                        id="telefone"
                        type="tel"
                        name="telefone"
                        value="{{ old('telefone') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        required
                        placeholder="(11) 99999-9999"
                    >
                    @error('telefone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Senha -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Senha
                    </label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        required
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Senha -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Confirmar Senha
                    </label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        required
                    >
                </div>

                <div class="mb-6 flex gap-3">
                    <a
                        href="{{ route('onboarding.step1') }}"
                        class="flex w-1/3 justify-center rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                    >
                        Voltar
                    </a>
                    <button
                        type="submit"
                        class="flex w-2/3 justify-center rounded-md bg-blue-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                    >
                        Continuar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
