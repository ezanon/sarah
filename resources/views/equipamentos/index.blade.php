@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3> Equipamentos de Grande Porte</h3>
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
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <span class="text-muted fs-1"></span>
                        </div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $eq->nome }}</h5>
                        <p class="text-muted small mb-1">
                            <strong>Lab:</strong> {{ $eq->laboratorio->nome }} 
                            @if($eq->laboratorio->centro) <span class="badge bg-secondary">{{ $eq->laboratorio->centro->sigla }}</span> @endif
                        </p>
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