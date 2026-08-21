@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>🔬 Equipamentos de Grande Porte</h3>
        <div>
            <a href="{{ route('equipamentos.create') }}" class="btn btn-primary">+ Novo Equipamento</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">
        @forelse($equipamentos as $eq)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    @if($eq->foto_url)
                        <img src="{{ $eq->foto_url }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="{{ $eq->nome }}">
                    @else
                        <div class="card-img-top d-flex align-items-center justify-content-center" 
                             style="height: 200px; background: #e9ecef; border: 1px dashed #bbb;">
                            <div class="text-center text-muted">
                                <div style="font-size: 2.5rem; margin-bottom: 5px;">📷</div>
                                <small>Sem imagem disponível</small>
                            </div>
                        </div>
                    @endif
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">{{ $eq->nome }}</h5>
                            @if($eq->ativo)
                                <span class="badge bg-success text-white">Ativo</span>
                            @else
                                <span class="badge bg-danger text-white">Inativo</span>
                            @endif
                        </div>

                        {{-- Badges do Centro e Laboratório --}}
                        <div class="mb-1">
                            @if($eq->laboratorio->centro->sigla)
                                <span class="badge bg-primary text-white me-1">{{ $eq->laboratorio->centro->sigla }}</span>
                            @endif
                            @if($eq->laboratorio->sigla)
                                <span class="badge bg-success text-white">{{ $eq->laboratorio->sigla }}</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-2">{{ $eq->laboratorio->nome }}</p>

                        @if($eq->patrimonio)
                            <p class="small mb-1"><strong>Patrimônio:</strong> {{ $eq->patrimonio }}</p>
                        @endif
                        <p class="small text-muted mb-3">
                            <strong>Responsáveis:</strong> {{ $eq->responsaveis->count() }}
                        </p>
                        <a href="{{ route('equipamentos.show', $eq) }}" class="btn btn-sm btn-outline-primary w-100">Ver Detalhes</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">Nenhum equipamento cadastrado ainda.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection