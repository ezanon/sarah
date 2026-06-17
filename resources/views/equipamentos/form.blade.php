<div class="mb-3">
    <label class="form-label fw-bold">Laboratório *</label>
    <select name="laboratorio_id" class="form-select form-select-lg @error('laboratorio_id') is-invalid @enderror" required>
        <option value="">Selecione o laboratório...</option>
        @foreach($laboratorios as $lab)
            <option value="{{ $lab->id }}" {{ (old('laboratorio_id', $equipamento->laboratorio_id ?? '') == $lab->id) ? 'selected' : '' }}>
                {{ $lab->centro->sigla ?? '-' }} ({{ $lab->sigla ?? 'sem sigla' }}) {{ $lab->nome }}
            </option>
        @endforeach
    </select>
    @error('laboratorio_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label fw-bold">Nome do Equipamento *</label>
        <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $equipamento->nome ?? '') }}" required>
        @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Nº Patrimônio</label>
        <div class="input-group">
            <input type="text" id="patrimonio" name="patrimonio" class="form-control @error('patrimonio') is-invalid @enderror" value="{{ old('patrimonio', $equipamento->patrimonio ?? '') }}" placeholder="Ex: 1234567">
            <button type="button" class="btn btn-outline-primary" id="btn-buscar-patrimonio"> Buscar</button>
        </div>
        <div id="patrimonio-feedback" class="form-text"></div>
        @error('patrimonio') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Marca</label>
        <input type="text" name="marca" class="form-control" value="{{ old('marca', $equipamento->marca ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Modelo</label>
        <input type="text" name="modelo" class="form-control" value="{{ old('modelo', $equipamento->modelo ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Ano Aquisição</label>
        <input type="number" name="ano_aquisicao" class="form-control" min="1900" max="2100" value="{{ old('ano_aquisicao', $equipamento->ano_aquisicao ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Ano Incorporação</label>
        <input type="number" name="ano_incorporacao" class="form-control" min="1900" max="2100" value="{{ old('ano_incorporacao', $equipamento->ano_incorporacao ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Valor (R$)</label>
        <input type="number" step="0.01" name="valor" class="form-control" value="{{ old('valor', $equipamento->valor ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Financiamento</label>
    <input type="text" name="financiamento" class="form-control" value="{{ old('financiamento', $equipamento->financiamento ?? '') }}" placeholder="Ex: FAPESP, CAPES, USP">
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Cód. Processo Convênio</label>
        <input type="text" name="cod_processo_convenio" class="form-control" value="{{ old('cod_processo_convenio', $equipamento->cod_processo_convenio ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Cód. Processo Incorporação</label>
        <input type="text" name="cod_processo_incorporacao" class="form-control" value="{{ old('cod_processo_incorporacao', $equipamento->cod_processo_incorporacao ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Foto do Equipamento</label>
    @if(isset($equipamento) && $equipamento->foto_url)
        <div class="mb-2">
            <img src="{{ $equipamento->foto_url }}" class="img-thumbnail" style="max-height: 150px;">
            <div class="form-check mt-1">
                <input class="form-check-input" type="checkbox" name="remover_foto" id="remover_foto">
                <label class="form-check-label text-danger" for="remover_foto">Remover foto atual</label>
            </div>
        </div>
    @endif
    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png">
    @error('foto') <div class="text-danger small">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Responsáveis (Codpes)</label>
    <textarea name="responsaveis_codpes" class="form-control" rows="2" placeholder="Digite os números USP separados por vírgula (Ex: 123456, 789012)">{{ old('responsaveis_codpes', isset($equipamento) ? $equipamento->responsaveis->pluck('codpes')->implode(', ') : '') }}</textarea>
    <div class="form-text">Usuários listados aqui poderão editar e excluir este equipamento.</div>
</div>

@push('scripts')
<script>
document.getElementById('btn-buscar-patrimonio').addEventListener('click', async function() {
    const patrimonio = document.getElementById('patrimonio').value;
    const feedback = document.getElementById('patrimonio-feedback');
    const btn = this;

    if (!patrimonio) {
        feedback.textContent = 'Digite um número de patrimônio.';
        feedback.className = 'form-text text-warning';
        return;
    }

    btn.disabled = true;
    feedback.textContent = 'Buscando no Replicado...';
    feedback.className = 'form-text text-info';

    try {
        const response = await fetch(`/equipamentos/buscar-patrimonio?patrimonio=${encodeURIComponent(patrimonio)}`);
        const data = await response.json();

        if (response.ok) {
            // Preenche os campos
            if(data.ano_incorporacao) document.querySelector('input[name="ano_incorporacao"]').value = data.ano_incorporacao;
            if(data.cod_processo_incorporacao) document.querySelector('input[name="cod_processo_incorporacao"]').value = data.cod_processo_incorporacao;
            if(data.marca) document.querySelector('input[name="marca"]').value = data.marca;
            if(data.modelo) document.querySelector('input[name="modelo"]').value = data.modelo;
            if(data.valor) document.querySelector('input[name="valor"]').value = data.valor;
            
            feedback.textContent = 'Dados preenchidos automaticamente!';
            feedback.className = 'form-text text-success';
        } else {
            feedback.textContent = data.error || 'Erro na busca.';
            feedback.className = 'form-text text-danger';
        }
    } catch (e) {
        feedback.textContent = 'Erro de conexão.';
        feedback.className = 'form-text text-danger';
    } finally {
        btn.disabled = false;
    }
});
</script>
@endpush