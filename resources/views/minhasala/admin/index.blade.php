@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">⚙️ Gerenciamento de Opções (Admin)</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        {{-- TIPOS DE SALA --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">Tipos de Sala</div>
                <div class="card-body">
                    <form action="{{ route('minhasala.admin.store.tipo') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="nome" class="form-control" placeholder="Ex: Laboratório, Gabinete..." required>
                            <button class="btn btn-success">Adicionar</button>
                        </div>
                    </form>
                    <ul class="list-group">
                        @forelse($tipos as $t)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $t->nome }}
                                <span class="badge bg-secondary">{{ $t->salas->count() }} vinculadas</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Nenhum cadastrado</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- BLOCOS --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">Blocos</div>
                <div class="card-body">
                    <form action="{{ route('minhasala.admin.store.bloco') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="nome" class="form-control" placeholder="Ex: A, B, C, Principal..." required>
                            <button class="btn btn-info">Adicionar</button>
                        </div>
                    </form>
                    <ul class="list-group">
                        @forelse($blocos as $b)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $b->nome }}
                                <span class="badge bg-secondary">{{ $b->salas->count() }} vinculadas</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Nenhum cadastrado</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- ANDARES --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">Andares</div>
                <div class="card-body">
                    <form action="{{ route('minhasala.admin.store.andar') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="numero" class="form-control" placeholder="Ex: Térreo, 1, 2, Subsolo..." required>
                            <button class="btn btn-warning">Adicionar</button>
                        </div>
                    </form>
                    <ul class="list-group">
                        @forelse($andares as $a)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $a->numero }}
                                <span class="badge bg-secondary">{{ $a->salas->count() }} vinculadas</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Nenhum cadastrado</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection