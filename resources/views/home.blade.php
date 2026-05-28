@extends('layouts.app') {{-- Remova se não utilizar layout --}}

@section('content')
<div class="container mx-auto px-4 py-10 max-w-md">
    @auth
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <h2 class="text-xl font-bold text-green-800">
                Bem-vindo, {{ auth()->user()->name }}!
            </h2>
            <p class="mt-2 text-green-700">Você está autenticado com a Senha Única USP.</p>
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-lg p-6 text-center shadow-sm">
            <p class="mt-4 text-gray-600">
                Você ainda não fez seu login com a sua
                <a href="{{ route('login') }}" class="bg-blue-600 font-medium rounded-lg hover:bg-blue-700 transition">Senha Única USP</a>
            </p>
        </div>
    @endauth
</div>
@endsection