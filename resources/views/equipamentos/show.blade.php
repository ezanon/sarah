@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>🔬 {{ $equipamento->nome }}</h3>
        @if($equipamento->podeEditar(auth()->user()))
            <div>
                <a href="{{ route('equipamentos.edit', $equipamento) }}" class="btn btn-warning">✏️ Editar</a>
                <form action="{{ route('equipamentos.destroy', $equipamento) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este equipamento?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">️ Excluir</button>
                </form>
            </div>
        @endif
    </div>

    <div class="row g-4">

<div class="col-md-4">
    @if($equipamento->foto_url)
        {{-- Imagem com efeito de hover --}}
        <img src="{{ $equipamento->foto_url }}" 
             class="img-fluid rounded shadow-sm" 
             alt="{{ $equipamento->nome }}"
             id="foto-equipamento"
             style="cursor: zoom-in; transition: transform 0.2s ease;"
             onmouseover="this.style.transform='scale(1.03)'"
             onmouseout="this.style.transform='scale(1)'">
        
        {{-- Lightbox Customizado (Fundo escuro em tela cheia) --}}
        <div id="lightbox-foto" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; justify-content: center; align-items: center; cursor: zoom-out;" onclick="this.style.display='none'">
            <img src="{{ $equipamento->foto_url }}" style="max-width: 90%; max-height: 90%; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5);">
        </div>

        {{-- Script para abrir o lightbox --}}
        <script>
            document.getElementById('foto-equipamento').addEventListener('click', function() {
                document.getElementById('lightbox-foto').style.display = 'flex';
            });
        </script>
    @else
        <div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" style="height: 300px;">
            <span class="text-muted fs-1"> Sem foto</span>
        </div>
    @endif
</div>
        
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary">Informações Gerais</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Centro/Laboratório:</strong> {{ $equipamento->laboratorio->centro->nome ?? '-' }} / {{ $equipamento->laboratorio->nome }}</li>
                        <li class="list-group-item"><strong>Marca/Modelo:</strong> {{ $equipamento->marca ?? '-' }} {{ $equipamento->modelo ? '(' . $equipamento->modelo . ')' : '' }}</li>
                        <li class="list-group-item"><strong>Patrimônio:</strong> {{ $equipamento->patrimonio ?? 'Não informado' }}</li>
                        <li class="list-group-item"><strong>Ano Aquisição:</strong> {{ $equipamento->ano_aquisicao ?? '-' }}</li>
                        <li class="list-group-item"><strong>Ano Incorporação:</strong> {{ $equipamento->ano_incorporacao ?? '-' }}</li>
                        <li class="list-group-item"><strong>Valor:</strong> {{ $equipamento->valor ? 'R$ ' . number_format($equipamento->valor, 2, ',', '.') : '-' }}</li>
                        <li class="list-group-item"><strong>Financiamento:</strong> {{ $equipamento->financiamento ?? '-' }}</li>
                        <li class="list-group-item"><strong>Proc. Convênio:</strong> {{ $equipamento->cod_processo_convenio ?? '-' }}</li>
                        <li class="list-group-item"><strong>Proc. Incorporação:</strong> {{ $equipamento->cod_processo_incorporacao ?? '-' }}</li>
                        <li class="list-group-item"><strong>Criado por:</strong> {{ $equipamento->criador->name ?? '-' }}</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h5 class="card-title text-primary">👥 Responsáveis</h5>
                    @if($equipamento->responsaveis->count() > 0)
                        <ul class="list-group">
                            @foreach($equipamento->responsaveis as $resp)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $resp->name }} <span class="badge bg-secondary">USP: {{ $resp->codpes }}</span></span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">Nenhum responsável adicional cadastrado.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection