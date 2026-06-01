@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    @auth
        {{-- Boas-vindas --}}
        <div class="mb-4">
            <h2 class="h4 mb-0">
                👋 Bem-vindo, <strong>{{ auth()->user()->name ?? auth()->user()->nompesttd }}</strong>!
            </h2>
            <small class="text-muted">Autenticado via Senha Única USP</small>
        </div>
    
        {{-- Box: Meus Veículos --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-secondary">🚗 Meus Veículos</h5>
                <a href="{{ route('veiculos.index') }}" class="btn btn-sm btn-outline-primary">
                    {{ $veiculos->isNotEmpty() ? 'Gerenciar' : 'Cadastrar' }}
                </a>
            </div>
            <div class="card-body p-0">
                @if($veiculos->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Placa</th>
                                    <th>Tipo</th>
                                    <th>Marca / Modelo</th>
                                    <th class="pe-3">Cor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($veiculos as $v)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge bg-primary text-white font-monospace fs-5 px-2 py-2" style="font-size: 0.9rem;">
                                                {{ strtoupper($v->placa) }}
                                            </span>
                                        </td>
                                        <td>{{ $v->tipo == 'carro' ? '🚙 Carro' : '🏍️ Moto' }}</td>
                                        <td>{{ $v->marca }} {{ $v->modelo }}</td>
                                        <td class="pe-3">{{ $v->cor }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-3 text-center text-muted">
                        Você ainda não cadastrou nenhum veículo. 
                        <a href="{{ route('veiculos.index') }}" class="text-decoration-none">Clique aqui para registrar</a>.
                    </div>
                @endif
            </div>
        </div>

        {{-- Box: Minha Sala --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-secondary">📍 Minha Sala</h5>
                <a href="{{ route('minhasala.index') }}" class="btn btn-sm btn-outline-primary">
                    {{ $minhaSala ? 'Editar' : 'Cadastrar' }}
                </a>
            </div>
            <div class="card-body">
                @if($minhaSala)
                    <div class="row g-2 small">
                        <div class="col-6 col-md-3">
                            <span class="d-block text-muted">Tipo</span>
                            <strong>{{ $minhaSala->tipo->nome ?? '—' }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="d-block text-muted">Bloco</span>
                            <strong>{{ $minhaSala->bloco->nome ?? '—' }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="d-block text-muted">Andar</span>
                            <strong>{{ $minhaSala->andar->numero ?? '—' }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="d-block text-muted">Sala</span>
                            <strong>{{ $minhaSala->numero }}</strong>
                        </div>
                        @if($minhaSala->descricao)
                            <div class="col-12 mt-2">
                                <span class="d-block text-muted">Descrição</span>
                                <span class="text-break">{{ $minhaSala->descricao }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="mb-0 text-muted">
                        Você ainda não cadastrou sua sala. 
                        <a href="{{ route('minhasala.index') }}" class="text-decoration-none">Clique aqui para registrar</a> e aparecer na área de contatos do IGc.
                    </p>
                @endif
            </div>
        </div>
    
        {{-- Box: Links Acadêmicos --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-secondary">🔗 Links Acadêmicos</h5>
                <a href="{{ route('links-academicos.index') }}" class="btn btn-sm btn-outline-primary">
                    {{ $links->isNotEmpty() ? 'Gerenciar' : 'Adicionar' }}
                </a>
            </div>
            <div class="card-body">
                @if($links->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($links as $link)
                            <a href="{{ $link->url }}" target="_blank" 
                               class="badge badge-success text-white text-decoration-none px-3 py-2 fs-6 mr-1 mt-1 mb-1"
                               title="{{ \App\Models\LinkAcademico::getPlataformas()[$link->plataforma]['nome'] }}">
                                {{-- Ícone removido --}}
                                {{ \App\Models\LinkAcademico::getPlataformas()[$link->plataforma]['nome'] }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="mb-0 text-muted">
                        Nenhum link acadêmico cadastrado. 
                        <a href="{{ route('links-academicos.index') }}" class="text-decoration-none">Adicione agora</a>.
                    </p>
                @endif
            </div>
        </div>

        {{-- Box: Nível CNPq --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-secondary">🎖️ Nível CNPq</h5>
                <a href="{{ route('cnpq.index') }}" class="btn btn-sm btn-outline-primary">Editar</a>
            </div>
            <div class="card-body">
                @if($nivelCnpq)
                    <span class="badge badge-success text-white fs-6 px-3 py-2">
                        {{ \App\Http\Controllers\CnpqController::NIVEIS[$nivelCnpq] ?? $nivelCnpq }}
                    </span>
                @else
                    <p class="mb-0 text-muted">
                        Nível CNPq não informado. 
                        <a href="{{ route('cnpq.index') }}" class="text-decoration-none">Clique aqui para registrar</a>.
                    </p>
                @endif
            </div>
        </div>
    
    
        {{-- Box: Minhas ODS --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-secondary">🌍 Minhas ODS</h5>
                <a href="{{ route('ods.index') }}" class="btn btn-sm btn-outline-primary">
                    {{ count($minhasOds) > 0 ? 'Editar' : 'Selecionar' }}
                </a>
            </div>
            <div class="card-body">
                @if(count($minhasOds) > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($minhasOds as $id)
                            @php $info = $odsList[$id]; @endphp
                            <a href="{{ route('ods.index') }}" class="d-inline-block mx-1 mb-2" title="{{ $info['nome'] }}">
                                <img src="{{ asset($info['img']) }}" alt="ODS {{ $id }}" style="max-height: 50px; width: auto;">
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="mb-0 text-muted">
                        Nenhuma ODS selecionada. 
                        <a href="{{ route('ods.index') }}" class="text-decoration-none">Selecione aqui</a> para alinhar seu trabalho aos objetivos globais.
                    </p>
                @endif
            </div>
        </div>

        {{-- Espaço para futuros resumos (links acadêmicos, etc.) --}}
        {{-- <div class="card shadow-sm border-0"> ... </div> --}}

    @else
        {{-- Não autenticado --}}
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <h3 class="h5 mb-3">Acesso ao Sistema SaRAh</h3>
                <p class="text-muted mb-4">
                    Você ainda não fez seu login com a Senha Única USP.
                </p>
                <a href="{{ route('login') }}" class="btn btn-primary px-4">
                    Login com Senha Única
                </a>
            </div>
        </div>
    @endauth
</div>
@endsection