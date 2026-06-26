@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3> Gerenciar Todos os Equipamentos</h3>
        <a href="{{ route('equipamentos.admin.index') }}" class="btn btn-outline-secondary">← Voltar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($equipamentos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th>Patrimônio</th>
                                <th>Local</th>
                                <th>Equipamento</th>
                                <th>Ano</th>
                                <th>Criado por</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equipamentos as $eq)
                                <tr>
                                    <td>
                                        <form action="{{ route('equipamentos.admin.equipamentos.toggle-ativo', $eq) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn btn-sm {{ $eq->ativo ? 'bg-success' : 'bg-danger' }} text-white" 
                                                    title="{{ $eq->ativo ? 'Clique para desativar' : 'Clique para ativar' }}"
                                                    style="min-width: 34px; padding: 0.25rem 0.5rem;">
                                                <i class="fa {{ $eq->ativo ? 'fa-check' : 'fa-times' }}"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        @if($eq->patrimonio)
                                            <span class="badge bg-light text-dark border">{{ $eq->patrimonio }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($eq->laboratorio->centro->sigla)
                                                <span class="badge bg-primary text-white">{{ $eq->laboratorio->centro->sigla }}</span>
                                            @endif
                                            @if($eq->laboratorio->sigla)
                                                <span class="badge bg-success text-white" style="margin-left: 1px;">{{ $eq->laboratorio->sigla }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td><em>{{ $eq->nome }}</em></td>
                                    <td>{{ $eq->ano_aquisicao ?? '-' }}</td>
                                    <td>{{ $eq->criador->name ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('equipamentos.edit', $eq) }}" class="btn btn-sm btn-outline-primary" title="Editar">✏️</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">Nenhum equipamento cadastrado.</p>
            @endif
        </div>
    </div>
</div>
@endsection