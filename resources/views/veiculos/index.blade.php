@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">🚗 Meus Veículos</h3>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FORMULÁRIO DE CADASTRO --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">Cadastrar Novo Veículo</div>
        <div class="card-body">
            <form action="{{ route('veiculos.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Placa</label>
                        <input type="text" name="placa" class="form-control text-uppercase" value="{{ old('placa') }}" required placeholder="ABC1D23">
                        @error('placa') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-control" required style="background-image: var(--bs-form-select-bg-img); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 16px 12px; padding-right: 2.25rem;">
                            <option value="">Selecione</option>
                            <option value="carro" {{ old('tipo') == 'carro' ? 'selected' : '' }}>🚙 Carro</option>
                            <option value="moto" {{ old('tipo') == 'moto' ? 'selected' : '' }}>🏍️ Moto</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Marca</label>
                        <input type="text" name="marca" class="form-control" value="{{ old('marca') }}" required placeholder="Ex: Fiat, Honda">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="modelo" class="form-control" value="{{ old('modelo') }}" required placeholder="Ex: Argo, CB 300">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cor</label>
                        <input type="text" name="cor" class="form-control" value="{{ old('cor') }}" required placeholder="Ex: Prata">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Salvar Veículo</button>
            </form>
        </div>
    </div>

    {{-- LISTA DE VEÍCULOS --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Placa</th>
                        <th>Tipo</th>
                        <th>Marca/Modelo</th>
                        <th>Cor</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
            <tbody>
                @forelse($veiculos as $v)
                    <tr>
                        <td>
                            <span class="badge bg-primary text-white font-monospace fs-4 px-2 py-2" style="font-size: 0.9rem;">
                                {{ strtoupper($v->placa) }}
                            </span>
                        </td>
                        <td>{{ $v->tipo == 'carro' ? '🚙 Carro' : '🏍️ Moto' }}</td>
                        <td>{{ $v->marca }} {{ $v->modelo }}</td>
                        <td>{{ $v->cor }}</td>
                        <td class="text-end">
                            <form action="{{ route('veiculos.destroy', $v->id) }}" method="POST" 
                                  onsubmit="return confirm('Tem certeza que deseja excluir este veículo?\nEsta ação não pode ser desfeita.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    🗑️ Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">Nenhum veículo cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DE CONFIRMAÇÃO --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja remover este veículo? Esta ação não pode ser desfeita.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Sim, excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- JS para injetar a rota no form do modal --}}
<script>
    document.getElementById('confirmDeleteModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const action = button.getAttribute('data-action');
        document.getElementById('deleteForm').setAttribute('action', action);
    });
</script>
@endsection