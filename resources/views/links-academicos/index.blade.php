@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">🔗 Links Acadêmicos</h3>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FORMULÁRIO --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">Adicionar / Atualizar Link</div>
        <div class="card-body">
            <form action="{{ route('links-academicos.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-5">
                    <label class="form-label">Plataforma</label>
                    <select name="plataforma" class="form-control" required>
                        <option value="">Selecione...</option>
                        @foreach($plataformas as $key => $info)
                            <option value="{{ $key }}" {{ old('plataforma') == $key ? 'selected' : '' }}>
                                {{ $info['nome'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Identificador <small class="text-muted">(sem URL)</small></label>
                    <input type="text" name="identificador" class="form-control" 
                           value="{{ old('identificador') }}" 
                           placeholder="Ex: 0000-0002-1234-5678" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Salvar</button>
                </div>
            </form>
            <small class="text-muted">
                💡 Dica: Digite apenas o código. Ex: para ResearcherID, use <code>A-0000-0000</code> (não a URL completa).
            </small>
        </div>
    </div>

    {{-- LISTA DE LINKS --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Plataforma</th>
                        <th>Identificador</th>
                        <th>Link</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($links as $link)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $plataformas[$link->plataforma]['nome'] }}
                                </span>
                            </td>
                            <td><code class="small">{{ $link->identificador }}</code></td>
                            <td>
                                <a href="{{ $link->url }}" target="_blank" class="text-decoration-none" title="Abrir link">
                                    🔗 Ver perfil
                                </a>
                            </td>
                            <td class="text-end">
                                <form action="{{ route('links-academicos.destroy', $link->id) }}" method="POST" 
                                      onsubmit="return confirm('Remover este link?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Nenhum link cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection