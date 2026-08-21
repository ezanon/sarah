@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>🔧 Gerenciar Todos os Equipamentos</h3>
        <div class="d-flex gap-2">
            {{-- Botão para ver o relatório (só aparece se o arquivo existir) --}}
            @if(file_exists(public_path('relatorios/equipamentos.pdf')))
                <a href="{{ asset('relatorios/equipamentos.pdf') }}" target="_blank" class="btn btn-outline-success mr-1">
                    👁️ Ver Relatório
                </a>
            @endif
            <a href="{{ route('equipamentos.admin.index') }}" class="btn btn-outline-secondary">← Voltar</a>
        </div>
    </div>

    {{-- Mensagens de feedback --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                                <th>Responsáveis</th>
                                <th></th>
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
                                        <div class="d-flex flex-column align-items-start">
                                            @if($eq->laboratorio->centro->sigla)
                                                <span class="badge bg-primary text-white mb-1">{{ $eq->laboratorio->centro->sigla }}</span>
                                            @endif
                                            @if($eq->laboratorio->sigla)
                                                <span class="badge bg-success text-white" style="margin-left: 1px;">{{ $eq->laboratorio->sigla }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <em>{{ $eq->nome }}</em>
                                        @if($eq->marca || $eq->modelo)
                                            <br>
                                            <small class="text-muted">{{ trim($eq->marca . ' ' . $eq->modelo) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $eq->ano_aquisicao ?? '-' }}</td>
                                    <td>
                                        @if($eq->responsaveis->count() > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($eq->responsaveis as $resp)
                                                    <span class="badge bg-secondary text-white mb-1 mr-1" style="font-weight: normal; font-size: 0.85rem;">
                                                        {{ $resp->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    
                                    {{-- COLUNA FOTO AJUSTADA --}}
                                    <td>
                                        @if($eq->foto)
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary btn-ver-foto" 
                                                    title="Ver foto"
                                                    data-foto="{{ $eq->foto_url }}"
                                                    data-nome="{{ addslashes($eq->nome) }}">
                                                <i class="fa fa-camera"></i>
                                            </button>
                                        @else
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-secondary" 
                                                    title="Sem foto"
                                                    disabled
                                                    style="opacity: 0.5; cursor: not-allowed;">
                                                {{-- Mesmo ícone, mas forçado a ficar cinza via CSS inline --}}
                                                <i class="fa fa-camera" style="color: #6c757d;"></i>
                                            </button>
                                        @endif
                                    </td>

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

{{-- LIGHTBOX GLOBAL (Mesma lógica do show.blade.php) --}}
<div id="lightbox-lista" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; justify-content: center; align-items: center; cursor: zoom-out;" onclick="this.style.display='none'">
    <div style="position: relative; max-width: 90%; max-height: 90%; display: flex; flex-direction: column; align-items: center;">
        <img id="img-lightbox-lista" src="" style="max-width: 100%; max-height: 85vh; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5);">
        <h5 id="titulo-lightbox-lista" style="color: white; text-align: center; margin-top: 15px; font-family: sans-serif; font-weight: normal;"></h5>
    </div>
</div>

<script>
    // Script para abrir o lightbox (Vanilla JS, igual ao show.blade.php)
    document.querySelectorAll('.btn-ver-foto').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var fotoUrl = this.getAttribute('data-foto');
            var nomeEquip = this.getAttribute('data-nome');
            
            // Atualiza a imagem e o título do lightbox global
            document.getElementById('img-lightbox-lista').src = fotoUrl;
            document.getElementById('titulo-lightbox-lista').textContent = nomeEquip;
            
            // Mostra o lightbox
            document.getElementById('lightbox-lista').style.display = 'flex';
        });
    });
</script>
@endsection