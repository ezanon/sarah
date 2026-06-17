@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3> Gerenciar Centros e Laboratórios</h3>
        <a href="{{ route('equipamentos.index') }}" class="btn btn-outline-secondary">← Voltar para Equipamentos</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- COLUNA: CENTROS --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Centros</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('equipamentos.admin.store.centro') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="nome" class="form-control" placeholder="Nome do centro" required>
                            <input type="text" name="sigla" class="form-control" placeholder="Sigla" style="max-width: 100px;">
                            <button type="submit" class="btn btn-primary">Adicionar</button>
                        </div>
                    </form>

                    <ul class="list-group">
                        @forelse($centros as $centro)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $centro->nome }}</strong>
                                    @if($centro->sigla)
                                        <span class="badge bg-secondary ms-2">{{ $centro->sigla }}</span>
                                    @endif
                                    <br>
                                    <small class="text-muted">{{ $centro->laboratorios->count() }} laboratório(s)</small>
                                </div>
                                <form action="{{ route('equipamentos.admin.destroy.centro', $centro) }}" method="POST" onsubmit="return confirm('Excluir este centro?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Nenhum centro cadastrado.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- COLUNA: LABORATÓRIOS --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Laboratórios</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('equipamentos.admin.store.laboratorio') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="mb-2">
                            <select name="centro_id" class="form-select" required>
                                <option value="">Selecione o centro...</option>
                                @foreach($centros as $centro)
                                    <option value="{{ $centro->id }}">{{ $centro->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group">
                            <input type="text" name="nome" class="form-control" placeholder="Nome do laboratório" required>
                            <input type="text" name="sigla" class="form-control" placeholder="Sigla" style="max-width: 100px;">
                            <button type="submit" class="btn btn-success">Adicionar</button>
                        </div>
                    </form>

                    <ul class="list-group">
                        @forelse($centros as $centro)
                            @foreach($centro->laboratorios as $lab)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $lab->nome }}</strong>
                                        @if($lab->sigla)
                                            <span class="badge bg-secondary ms-2">{{ $lab->sigla }}</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $centro->nome }}</small>
                                    </div>
                                    <form action="{{ route('equipamentos.admin.destroy.laboratorio', $lab) }}" method="POST" onsubmit="return confirm('Excluir este laboratório?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                    </form>
                                </li>
                            @endforeach
                        @empty
                            <li class="list-group-item text-muted">Nenhum laboratório cadastrado.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection