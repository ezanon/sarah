@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="alert alert-info">
        <strong>ℹ️ Importante:</strong> Estas informações serão utilizadas na área de contatos do Instituto de Geociências (IGc).
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Cadastrar / Atualizar Minha Sala</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('minhasala.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tipo de Sala</label>
                        <select name="tipo_sala_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t->id }}" {{ old('tipo_sala_id', $minhaSala?->tipo_sala_id) == $t->id ? 'selected' : '' }}>
                                    {{ $t->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bloco</label>
                        <select name="bloco_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            @foreach($blocos as $b)
                                <option value="{{ $b->id }}" {{ old('bloco_id', $minhaSala?->bloco_id) == $b->id ? 'selected' : '' }}>
                                    {{ $b->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Andar</label>
                        <select name="andar_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            @foreach($andares as $a)
                                <option value="{{ $a->id }}" {{ old('andar_id', $minhaSala?->andar_id) == $a->id ? 'selected' : '' }}>
                                    {{ $a->numero }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Número da Sala</label>
                        <input type="text" name="numero" class="form-control" value="{{ old('numero', $minhaSala?->numero) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Descrição (opcional)</label>
                        <input type="text" name="descricao" class="form-control" value="{{ old('descricao', $minhaSala?->descricao) }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Salvar Informações</button>
            </form>
        </div>
    </div>
</div>
@endsection