@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>📝 Gerar Relatórios dos Departamentos</h3>
        <a href="{{ route('relatorios.index') }}" class="btn btn-outline-secondary">← Voltar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('relatorios.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="departamento" class="form-label fw-bold">Departamento *</label>
                    <select name="departamento" id="departamento" class="form-select" required>
                        <option value="">-- Selecione o departamento --</option>
                        @foreach($departamentos as $sigla => $info)
                            <option value="{{ $sigla }}">{{ $sigla }} — {{ $info['nome'] ?? $sigla }}</option>
                        @endforeach
                    </select>
                    @error('departamento')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="ano" class="form-label fw-bold">Ano de referência *</label>
                    <input type="number" name="ano" id="ano" class="form-control" 
                           min="2000" max="{{ date('Y') }}" value="{{ date('Y') - 1 }}" required>
                    <div class="form-text">Informe o ano cujos dados devem constar no relatório.</div>
                    @error('ano')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-warning">
                    <strong>️ Atenção:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Se já existir um relatório para este departamento e ano, ele será <strong>sobrescrito</strong>.</li>
                        <li>O relatório será gerado em formato HTML para que a secretaria possa copiar e editar no Word.</li>
                        <li>A seção "Conselho de Departamento" virá em branco para preenchimento manual.</li>
                    </ul>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('relatorios.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success ml-1">🚀 Gerar Relatório</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection