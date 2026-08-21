<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Nº Patrimônio (000.000000)</label>
        <div class="input-group">
            <input type="text" id="patrimonio" name="patrimonio" class="form-control @error('patrimonio') is-invalid @enderror" 
                   value="{{ old('patrimonio', $equipamento->patrimonio ?? '') }}" 
                   placeholder="000.000000" 
                   maxlength="10"
                   pattern="\d{3}\.\d{6}"
                   style="font-family: monospace; letter-spacing: 2px;">
            <button type="button" class="btn btn-outline-primary" id="btn-buscar-patrimonio">🔍 Buscar</button>
        </div>
        <div id="patrimonio-feedback" class="form-text"></div>
        @error('patrimonio') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    
    <div class="col-md-8 mb-3">
        <label class="form-label fw-bold">Nome do Equipamento *</label>
        <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $equipamento->nome ?? '') }}" required>
        @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    
    <div id="aviso-preenchimento-automatico" class="alert alert-light border-start border-primary border-3 py-2 mt-0 mb-3" role="alert" style="display: none;">
        <small class="text-muted">
            💡 <strong>Dica:</strong> Os dados preenchidos automaticamente pela busca de patrimônio podem ser editados manualmente caso julgue necessário.
        </small>
    </div>
    
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
    <div class="col-md-6 mb-3">
        <label class="form-label">Ano Aquisição</label>
        <input type="number" name="ano_aquisicao" class="form-control" min="1900" max="2100" value="{{ old('ano_aquisicao', $equipamento->ano_aquisicao ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Ano Incorporação</label>
        <input type="number" name="ano_incorporacao" class="form-control" min="1900" max="2100" value="{{ old('ano_incorporacao', $equipamento->ano_incorporacao ?? '') }}">
    </div>    
</div>

<div class="row">    
    <div class="col-md-5">
        <label class="form-label">Financiamento</label>
        <input type="text" name="financiamento" class="form-control" value="{{ old('financiamento', $equipamento->financiamento ?? '') }}" placeholder="Ex: FAPESP, CAPES, USP">    
    </div>
    <div class="col-md-2">
        <label for="moeda" class="form-label">Moeda</label>
        <select class="form-select" id="moeda" name="moeda">
            <option value="BRL" {{ old('moeda', $equipamento->moeda ?? 'BRL') == 'BRL' ? 'selected' : '' }}>R$ (Real)</option>
            <option value="USD" {{ old('moeda', $equipamento->moeda ?? '') == 'USD' ? 'selected' : '' }}>US$ (Dólar)</option>
            <option value="EUR" {{ old('moeda', $equipamento->moeda ?? '') == 'EUR' ? 'selected' : '' }}>€ (Euro)</option>
        </select>
        @error('moeda') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-5">
        <label for="valor" class="form-label">Valor</label>
        <input type="number" step="0.01" min="0" class="form-control" 
               id="valor" name="valor" 
               value="{{ old('valor', $equipamento->valor ?? '') }}">
        @error('valor') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
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
    <label class="form-label fw-bold">Responsáveis (Número USP)</label>

    {{-- 📋 Visualização dos responsáveis atuais (Apenas na tela de Edição) --}}
    @if(isset($equipamento) && $equipamento->responsaveis->count() > 0)
        <div class="mb-2 p-3 bg-light rounded border">
            <small class="text-muted d-block mb-2 fw-medium">👥 Responsáveis atuais:</small>
            <div class="d-flex flex-wrap gap-2">
                @foreach($equipamento->responsaveis as $resp)
                    <span class="badge bg-primary text-white ml-1 d-flex align-items-center gap-1 py-2 px-3">
                        <span class="fw-bold">({{ $resp->codpes }}) </span>{{ $resp->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 📝 Campo de entrada (Textarea) --}}
    <textarea 
        name="responsaveis_codpes" 
        class="form-control @error('responsaveis_codpes') is-invalid @enderror" 
        rows="2" 
        placeholder="Digite os números USP separados por vírgula (Ex: 123456, 789012)"
    >{{ old('responsaveis_codpes', isset($equipamento) ? $equipamento->responsaveis->pluck('codpes')->implode(', ') : auth()->user()->codpes) }}</textarea>
    
    <div class="form-text">
        @if(!isset($equipamento))
            ✅ Seu número USP foi preenchido automaticamente como responsável principal.<br>
        @endif
            Adicione ou remova números USP <strong>separados por vírgula</strong> para atualizar a lista acima.
    </div>
    @error('responsaveis_codpes') <div class="invalid-feedback">{{ $message }}</div> @enderror
    
    <hr class="my-4">
    <label class="form-label fw-bold">⚠️ Status do Equipamento</label>
    <div class="mb-2 p-3 bg-light rounded border">
        <div class="col-md-4 d-flex align-items-center">
            <div class="form-check form-switch">
                {{-- Hidden input garante que o valor 0 seja enviado se o checkbox estiver desmarcado --}}
                <input type="hidden" name="ativo" value="0">
                <input class="form-check-input" type="checkbox" name="ativo" id="ativo" value="1" 
                       {{ old('ativo', $equipamento->ativo ?? true) ? 'checked' : '' }}>
                <label class="form-check-label fw-bold" for="ativo">
                    Equipamento Ativo
                </label>
            </div>
        </div>
        <div class="col-md-8">
            <label for="motivo_inativacao" class="form-label">Motivo da Inativação</label>
            <select name="motivo_inativacao" id="motivo_inativacao" class="form-select">
                <option value="">-- Selecione o motivo --</option>
                <option value="obsoleto" {{ old('motivo_inativacao', $equipamento->motivo_inativacao ?? '') == 'obsoleto' ? 'selected' : '' }}>Obsoleto</option>
                <option value="danificado" {{ old('motivo_inativacao', $equipamento->motivo_inativacao ?? '') == 'danificado' ? 'selected' : '' }}>Danificado</option>
                <option value="doado" {{ old('motivo_inativacao', $equipamento->motivo_inativacao ?? '') == 'doado' ? 'selected' : '' }}>Doado</option>
            </select>
            <div class="form-text">Obrigatório apenas se o equipamento for marcado como inativo.</div>
        </div>

</div>

{{-- 🎭 Script inline (sem @push) --}}
<script>
// 🎭 Máscara para o campo Patrimônio (formato: 999.999999)
document.addEventListener('DOMContentLoaded', function() {
    const inputPatrimonio = document.getElementById('patrimonio');
    
    if (inputPatrimonio) {
        inputPatrimonio.addEventListener('input', function(e) {
            let valor = e.target.value.replace(/\D/g, '');
            
            if (valor.length > 9) valor = valor.substring(0, 9);
            
            if (valor.length > 3) {
                valor = valor.substring(0, 3) + '.' + valor.substring(3);
            }
            
            e.target.value = valor;
        });
    }
});

// 🔍 Busca de patrimônio no Replicado
document.addEventListener('DOMContentLoaded', function() {
    const btnBuscar = document.getElementById('btn-buscar-patrimonio');
    const inputPatrimonio = document.getElementById('patrimonio');
    const feedback = document.getElementById('patrimonio-feedback');
    const aviso = document.getElementById('aviso-preenchimento-automatico');
    
    if (!btnBuscar || !inputPatrimonio) return;
    
    btnBuscar.addEventListener('click', async function() {
        const patrimonio = inputPatrimonio.value;

        if (!patrimonio) {
            feedback.textContent = '⚠️ Digite um número de patrimônio.';
            feedback.className = 'form-text text-warning';
            return;
        }

        btnBuscar.disabled = true;
        btnBuscar.textContent = '⏳ Buscando...';
        feedback.textContent = '🔍 Buscando no Replicado...';
        feedback.className = 'form-text text-info';

        try {
            const url = `/equipamentos/buscar-patrimonio?patrimonio=${encodeURIComponent(patrimonio)}`;
            const response = await fetch(url);
            const data = await response.json();

            if (response.ok) {
                // Preenche os campos retornados
                if (data.ano_incorporacao) {
                    document.querySelector('input[name="ano_incorporacao"]').value = data.ano_incorporacao;
                }
                if (data.cod_processo_incorporacao) {
                    document.querySelector('input[name="cod_processo_incorporacao"]').value = data.cod_processo_incorporacao;
                }
                if (data.marca) {
                    document.querySelector('input[name="marca"]').value = data.marca;
                }
                if (data.modelo) {
                    document.querySelector('input[name="modelo"]').value = data.modelo;
                }
                if (data.cod_processo_convenio) {
                    document.querySelector('input[name="cod_processo_convenio"]').value = data.cod_processo_convenio;
                }
                if (data.valor) {
                    document.querySelector('input[name="valor"]').value = data.valor;
                }
                
                // Exibe o aviso de que os dados podem ser editados
                if (aviso) {
                    aviso.style.display = 'block';
                }
                
                feedback.textContent = '✅ Dados preenchidos automaticamente!';
                feedback.className = 'form-text text-success';
            } else {
                // Em caso de erro, mantém o aviso oculto
                if (aviso) {
                    aviso.style.display = 'none';
                }
                
                feedback.textContent = data.error || '❌ Erro na busca.';
                feedback.className = 'form-text text-danger';
            }
        } catch (e) {
            if (aviso) {
                aviso.style.display = 'none';
            }
            
            feedback.textContent = '❌ Erro de conexão: ' + e.message;
            feedback.className = 'form-text text-danger';
        } finally {
            btnBuscar.disabled = false;
            btnBuscar.textContent = '🔍 Buscar';
        }
    });
});
</script>