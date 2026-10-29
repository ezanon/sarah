@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>📊 Relatórios dos Departamentos</h3>
        <a href="{{ route('relatorios.create') }}" class="btn btn-primary">➕ Gerar Novo Relatório</a>
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

    @if($relatorios->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Departamento</th>
                                <th>Ano</th>
                                <th>Gerado em</th>
                                <th>Gerado por</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($relatorios as $rel)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary text-white">{{ $rel->departamento }}</span>
                                    </td>
                                    <td><strong>{{ $rel->ano }}</strong></td>
                                    <td>{{ $rel->gerado_em->format('d/m/Y H:i') }}</td>
                                    <td>{{ $rel->user->name ?? '-' }}</td>
                                    <td>
                                        <a href="{{ $rel->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            ️ Ver Relatório
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info text-center">
            Nenhum relatório gerado ainda. 
            <a href="{{ route('relatorios.create') }}">Clique aqui</a> para gerar o primeiro.
        </div>
    @endif
</div>
@endsection